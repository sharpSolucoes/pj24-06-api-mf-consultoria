<?php
class Clients_Reports extends API_configuration
{
    private $clients_reports_groups;

    function __construct()
    {
        parent::__construct();
        $this->clients_reports_groups = new Clients_Reports_Groups();
    }

    public function create(object $parms)
    {
        $client_id = $parms->clientId;
        $client_reports_group_id = $parms->groupId;
        $description = $parms->description;
        $url = $parms->url;

        $values = '
        "' . $client_id . '",
        "' . $client_reports_group_id . '",
        "' . $description . '",
        "' . $url . '"
      ';

        $sql = 'INSERT INTO `clients_reports` (`client_id`, `client_reports_group_id`, `client_report_description`, `client_report_url`) VALUES (' . $values . ')';
        $create_client_report = $this->db_create($sql);
        if ($create_client_report) {
            $this->generate_user_log("client.create_report");
            return ['message' => "Report created successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error creating report"];
        }
    }

    public function read_by_client_id(
        int $client_id
    ) {
        $sql = "SELECT `client_report_id` AS 'id', `client_reports_group_id` AS `groupId`, `client_report_description` AS 'description', `client_report_url` AS 'url' FROM `clients_reports` WHERE `client_id` = " . $client_id . " AND `client_report_is_deleted` = 'false'";
        $get_reports = $this->db_read($sql);
        if ($this->db_num_rows($get_reports) > 0) {
            $reports = [];
            while ($report = $this->db_object($get_reports)) {
                $report->id = (int) $report->id;

                $group_this_report = $this->clients_reports_groups->read_by_id($report->groupId);
                $report->group = $group_this_report;
                unset($report->groupId);

                array_push($reports, $report);
            }

            return $reports;
        } else {
            return [];
        }
    }

    public function read_by_url(
        object $parms
    ) {
        $group_name = $parms->group;
        $group_name = str_replace("_", " ", $parms->group);
        $report_name = str_replace("_", " ",  $parms->report);

        $client_id = $parms->clientId;

        $group = $this->clients_reports_groups->read_by_name_and_client_id($group_name, $client_id);
        if ($group !== null) {
            $sql = "SELECT `client_report_id` AS 'id', `client_report_description` AS 'description', `client_report_url` AS 'url' FROM `clients_reports` WHERE `client_reports_group_id` = " . $group->id . " AND `client_report_description` = '" . $report_name . "' AND `client_id` = '" . $client_id . "' AND`client_report_is_deleted` = 'false'";
            $get_report = $this->db_read($sql);
            if ($this->db_num_rows($get_report) > 0) {
                $report = $this->db_object($get_report);
                $report->id = (int) $report->id;

                $report->group = [
                    'id' => $group->id,
                    'description' => $group->description
                ];

                return $report;
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(404);
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $client_reports_group_id = $parms->groupId;
        $description = $parms->description;
        $url = $parms->url;

        $values = '
            `client_reports_group_id` = "' . $client_reports_group_id . '",
            `client_report_description` = "' . $description . '",
            `client_report_url` = "' . $url . '"';

        $sql = 'UPDATE `clients_reports` SET ' . $values . ' WHERE `client_report_id` = ' . $id;
        $update_client_report = $this->db_update($sql);
        if ($update_client_report) {
            $this->generate_user_log("client.update_report");
            return ['message' => "Report updated successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error updating report"];
        }
    }

    public function delete(int $id)
    {
        $sql = "UPDATE `clients_reports` SET `client_report_is_deleted` = 'true' WHERE `client_report_id` = " . $id;
        $delete_client_report = $this->db_delete($sql);
        if ($delete_client_report) {
            $this->generate_user_log("client.delete_report");
            return ['message' => "Report deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting report"];
        }
    }
}
