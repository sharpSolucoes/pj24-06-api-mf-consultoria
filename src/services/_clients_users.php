<?php
class Clients_Users extends API_configuration
{
    public function create(object $parms)
    {
        $client_id = $parms->clientId;
        $name = $parms->name;
        $email = $parms->email;
        $phone = $parms->phone;

        if ($this->verify_exist_email($email)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        "' . $client_id . '",
        "' . $name . '",
        "' . $email . '",
        "' . $phone . '"
        ';

        $sql = 'INSERT INTO `clients_users` (`client_id`, `client_user_name`, `client_user_email`, `client_user_phone`) VALUES (' . $values . ')';
        $create_client_user = $this->db_create($sql);
        if ($create_client_user) {
            $client_user_id = $create_client_user;

            $slug = $this->slugify($client_user_id . '-' . $name);
            $sql = 'UPDATE `clients_users` SET `client_user_slug` = "' . $slug . '" WHERE `client_user_id` = ' . $client_user_id;
            $this->db_update($sql);

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
        $sql = "SELECT `client_user_id` AS 'id', `client_user_name` AS 'name', `client_user_email` AS 'email', `client_user_phone` AS 'phone' FROM `clients_users` WHERE `client_id` = " . $client_id . " AND `client_user_is_deleted` = 'false'";
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

    public function update(object $parms)
    {
        $id = $parms->id;
        $name = $parms->name;
        $email = $parms->email;
        $phone = $parms->phone;

        if ($this->verify_exist_email($email, $id)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
            `client_user_name` = "' . $name . '",
            `client_user_email` = "' . $email . '",
            `client_user_phone` = "' . $phone . '"';

        $sql = 'UPDATE `clients_users` SET ' . $values . ' WHERE `client_user_id` = ' . $id;
        $update_client_user = $this->db_update($sql);
        if ($update_client_user) {
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
            $this->generate_user_log("client.delete_user");
            return ['message' => "User deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting user"];
        }
    }

    protected function verify_exist_email(string $email, string $id = "")
    {
        $sql = 'SELECT `client_id` AS "id" FROM `clients` WHERE `client_email` = "' . $email . '" AND `client_is_deleted` = "false"';
        $get_client_email = $this->db_read($sql);
        if ($this->db_num_rows($get_client_email) == 0) {
            $sql = 'SELECT `client_user_id` AS "id" FROM `clients_users` WHERE `client_user_email` = "' . $email . '" AND `client_user_is_deleted` = "false"';
            $get_user_email = $this->db_read($sql);
            if ($this->db_num_rows($get_user_email) > 0) {
                $user_data = $this->db_object($get_user_email);
                if ($user_data->id == $id) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
