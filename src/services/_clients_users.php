<?php
class Clients_Users extends API_configuration
{
    private $emails;

    function __construct()
    {
        parent::__construct();
        $this->emails = new Clients_Emails();
    }

    public function create(object $parms)
    {
        $client_id = $parms->clientId;
        $name = $parms->name;
        $position = $parms->position;
        $email = $parms->email;
        $phone = $parms->phone;

        if ($this->emails->read_by_email($email)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        "' . $client_id . '",
        "' . $name . '",
        "' . $position . '",
        "' . $email . '",
        "' . $phone . '"
        ';

        $sql = 'INSERT INTO `clients_users` (`client_id`, `client_user_name`, `client_user_position`, `client_user_email`, `client_user_phone`) VALUES (' . $values . ')';
        $create_client_user = $this->db_create($sql);
        if ($create_client_user) {
            $client_user_id = $create_client_user;

            $slug = $this->slugify($client_user_id . '-' . $name);
            $sql = 'UPDATE `clients_users` SET `client_user_slug` = "' . $slug . '" WHERE `client_user_id` = ' . $client_user_id;
            $this->db_update($sql);

            $this->emails->create("client_user", $client_user_id, $email);

            $this->generate_user_log("client.create_user");
            return ['message' => "User created successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error creating user"];
        }
    }

    public function read_by_client_id(
        int $client_id
    ) {
        $sql = "SELECT `client_user_id` AS 'id', `client_user_name` AS 'name', `client_user_position` AS 'position', `client_user_email` AS 'email', `client_user_phone` AS 'phone' FROM `clients_users` WHERE `client_id` = " . $client_id . " AND `client_user_is_deleted` = 'false'";
        $get_users = $this->db_read($sql);
        if ($this->db_num_rows($get_users) > 0) {
            $users = [];
            while ($user = $this->db_object($get_users)) {
                $user->id = (int) $user->id;
                array_push($users, $user);
            }

            return $users;
        } else {
            return [];
        }
    }

    public function read_by_id(
        int $id
    ) {
        $sql = "SELECT `client_user_id` AS 'id', `client_user_name` AS 'name', `client_user_position` AS 'position', `client_user_email` AS 'email', `client_user_phone` AS 'phone', `client_user_is_deleted` AS 'isDeleted' FROM `clients_users` WHERE `client_user_id` = " . $id;
        $get_user = $this->db_read($sql);
        if ($this->db_num_rows($get_user) > 0) {
            $user = $this->db_object($get_user);
            $user->id = (int) $user->id;
            return $user;
        } else {
            return [];
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $name = $parms->name;
        $position = $parms->position;
        $email = $parms->email;
        $phone = $parms->phone;

        if ($this->emails->read_by_email($email) && ($this->emails->read_by_email($email)->client_id != "" || ($this->emails->read_by_email($email)->client_user_id != "" && $this->emails->read_by_email($email)->client_user_id != $id))) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
            `client_user_name` = "' . $name . '",
            `client_user_position` = "' . $position . '",
            `client_user_email` = "' . $email . '",
            `client_user_phone` = "' . $phone . '"';

        $sql = 'UPDATE `clients_users` SET ' . $values . ' WHERE `client_user_id` = ' . $id;
        $update_client_user = $this->db_update($sql);
        if ($update_client_user) {

            $this->emails->update("client_user", $id, $email);

            $this->generate_user_log("client.update_user");

            return ['message' => "User updated successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error updating user"];
        }
    }

    public function delete(int $id)
    {
        $sql = "UPDATE `clients_users` SET `client_user_is_deleted` = 'true' WHERE `client_user_id` = " . $id;
        $delete_client_user = $this->db_delete($sql);
        if ($delete_client_user) {
            $client_user = $this->read_by_id($id);
            $this->emails->delete($client_user->email);

            $this->generate_user_log("client.delete_user");
            
            return ['message' => "User deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting user"];
        }
    }
}
