<?php
class Clients_Reports extends API_configuration
{
    public function create(object $parms)
    {
        $client_id = $parms->clientId;
        $description = $parms->description;
        $url = $parms->url;

        $values = '
        "' . $client_id . '",
        "' . $description . '",
        "' . $url . '"
      ';

        $sql = 'INSERT INTO `clients_reports` (`client_id`, `client_report_description`, `client_report_url`) VALUES (' . $values . ')';
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
        $sql = "SELECT `client_report_id` AS 'id', `client_report_description` AS 'description', `client_report_url` AS 'url' FROM `clients_reports` WHERE `client_id` = " . $client_id . " AND `client_report_is_deleted` = 'false'";
        $get_reports = $this->db_read($sql);
        if ($this->db_num_rows($get_reports) > 0) {
            $reports = [];
            while ($report = $this->db_object($get_reports)) {
                $report->id = (int) $report->id;
                array_push($reports, $report);
            }

            return $reports;
        } else {
            return [];
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $description = $parms->description;
        $url = $parms->url;

        $values = '
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
