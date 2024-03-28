<?php
class Clients_Reports_Groups extends API_configuration
{
    public function create(object $parms)
    {
        $client_id = $parms->clientId;
        $description = $parms->description;

        $values = '
        "' . $client_id . '",
        "' . $description . '"
      ';

        $sql = 'INSERT INTO `clients_reports_groups` (`client_id`, `client_reports_group_description`) VALUES (' . $values . ')';
        $create_client_reports_group = $this->db_create($sql);
        if ($create_client_reports_group) {
            $this->generate_user_log("client.create_reports_group");
            return ['message' => "Reports group created successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error creating reports group"];
        }
    }

    public function read_by_client_id(
        int $client_id
    ) {
        $sql = "SELECT `client_reports_group_id` AS 'id', `client_reports_group_description` AS 'description' FROM `clients_reports_groups` WHERE `client_id` = '" . $client_id . "' AND `client_reports_group_is_deleted` = 'false'";
        $get_reports_groups = $this->db_read($sql);
        if ($this->db_num_rows($get_reports_groups) > 0) {
            $reports_groups = [];
            while ($reports_group = $this->db_object($get_reports_groups)) {
                $reports_group->id = (int) $reports_group->id;
                array_push($reports_groups, $reports_group);
            }

            return $reports_groups;
        } else {
            return [];
        }
    }

    public function read_by_id(int $id)
    {
        $sql = "SELECT `client_reports_group_id` AS 'id', `client_reports_group_description` AS 'description' FROM `clients_reports_groups` WHERE `client_reports_group_id` = " . $id . " AND `client_reports_group_is_deleted` = 'false'";
        $get_reports_group = $this->db_read($sql);
        if ($this->db_num_rows($get_reports_group) > 0) {
            $reports_group = $this->db_object($get_reports_group);
            $reports_group->id = (int) $reports_group->id;

            return $reports_group;
        } else {
            return [];
        }
    }

    public function read_by_name_and_client_id(string $name, int $client_id)
    {
        $sql = "SELECT `client_reports_group_id` AS 'id', `client_reports_group_description` AS 'description' FROM `clients_reports_groups` WHERE `client_reports_group_description` = '" . $name . "' AND `client_id` = " . $client_id . " AND `client_reports_group_is_deleted` = 'false'";
        $get_reports_group = $this->db_read($sql);
        if ($this->db_num_rows($get_reports_group) > 0) {
            $reports_group = $this->db_object($get_reports_group);
            $reports_group->id = (int) $reports_group->id;

            return $reports_group;
        } else {
            return [];
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $description = $parms->description;

        $values = '
            `client_reports_group_description` = "' . $description . '"';

        $sql = 'UPDATE `clients_reports_groups` SET ' . $values . ' WHERE `client_reports_group_id` = ' . $id;
        $update_client_reports_group = $this->db_update($sql);
        if ($update_client_reports_group) {
            $this->generate_user_log("client.update_reports_group");
            return ['message' => "Reports group updated successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error updating reports group"];
        }
    }

    public function delete(int $id)
    {
        $sql = "UPDATE `clients_reports_groups` SET `client_reports_group_is_deleted` = 'true' WHERE `client_reports_group_id` = " . $id;
        $delete_client_reports_group = $this->db_delete($sql);
        if ($delete_client_reports_group) {
            $this->generate_user_log("client.delete_reports_group");
            return ['message' => "Reports group deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting reports group"];
        }
    }
}
