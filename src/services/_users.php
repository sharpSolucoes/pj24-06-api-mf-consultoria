<?php
class Users extends API_configuration
{
    public function create(object $parms)
    {
        $name = $parms->name;
        $email = $parms->email;
        $phone = $parms->phone;
        $password_confirmation = $parms->passwordConfirmation;
        $permissions = (array) $parms->permissions;
        $status = $parms->status;

        if ($this->verify_exist_email($email)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        "' . $name . '",
        "' . $email . '",
        "' . $phone . '",
        "' . password_hash($password_confirmation, PASSWORD_BCRYPT, ['cost' => 12]) . '",
        "' . $status . '"
        ';

        $sql = 'INSERT INTO `users` (`user_name`, `user_email`, `user_phone`, `user_password`, `user_status`) VALUES (' . $values . ')';
        $create_user = $this->db_create($sql);
        if ($create_user) {
            $user_id = $create_user;

            $slug = $this->slugify($user_id . '-' . $name);
            $sql = 'UPDATE `users` SET `user_slug` = "' . $slug . '" WHERE `user_id` = ' . $user_id;
            $this->db_update($sql);

            for ($i = 0; $i < count($permissions); $i++) {
                $permission_key = array_keys($permissions);
                $permission_data = (array) $permissions[$permission_key[$i]];

                for ($j = 0; $j < count($permission_data); $j++) {
                    $permission_data_key = array_keys($permission_data);
                    $permission_data_value = $permission_data[$permission_data_key[$j]] ? "true" : "false";

                    $permission = $permission_key[$i] . '.' . $permission_data_key[$j];
                    $sql = 'UPDATE `users_permissions` SET `user_permission_status` = "' . $permission_data_value . '" WHERE `user_id` = ' . $user_id . ' AND `user_permission_permission` = "' . $permission . '"';
                    $this->db_update($sql);
                }
            }

            $this->generate_user_log("users.create");
            return ['message' => "User created successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error creating user"];
        }
    }

    public function read()
    {
        $sql = 'SELECT `user_id` AS "id", `user_name` AS "name", `user_phone` AS "phone", `user_slug` AS "slug", `user_status` AS "status" FROM `users` WHERE `user_is_deleted` = "false"';
        $get_users_data = $this->db_read($sql);
        if ($this->db_num_rows($get_users_data) > 0) {
            $users_data = [];
            while ($user_data = $this->db_object($get_users_data)) {
                $user_data->id = (int) $user_data->id;
                $user_data->status = $user_data->status == 'true' ? true : false;
                $user_data->name = mb_convert_case($user_data->name, MB_CASE_TITLE, 'UTF-8');

                array_push($users_data, $user_data);
            }

            $this->generate_user_log("users.read");
            return $users_data;
        } else {
            return [];
        }
    }

    public function read_by_slug(string $slug)
    {
        $sql = 'SELECT `user_id` AS "id", `user_name` AS "name", `user_email` AS "email", `user_phone` AS "phone", `user_status` AS "status" FROM `users` WHERE `user_slug` = "' . $slug . '"';
        $get_user_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_data) > 0) {
            $user_data = $this->db_object($get_user_data);
            $user_data->id = (int) $user_data->id;
            $user_data->status = $user_data->status == 'true' ? true : false;

            $sql = 'SELECT `user_permission_permission` AS "permission", `user_permission_status` AS "status" FROM `users_permissions` WHERE `user_id` = ' . $user_data->id;
            $user_permissions = $this->db_read($sql);
            if ($this->db_num_rows($user_permissions) > 0) {
                $permissions = [];
                while ($user_permission = $this->db_object($user_permissions)) {
                    $permission = explode('.', $user_permission->permission);
                    $permissions[$permission[0]][$permission[1]] = $user_permission->status == 'true' ? true : false;
                }
                $user_data->permissions = $permissions;
            }

            return $user_data;
        } else {
            http_response_code(404);
            return ['message' => "User not found"];
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $name = $parms->name;
        $email = $parms->email;
        $phone = $parms->phone;
        $password_confirmation = $parms->passwordConfirmation;
        $changePassword = $parms->changePassword;
        $permissions = (array) $parms->permissions;
        $status = $parms->status;

        if ($this->verify_exist_email($email, $id)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        `user_name` = "' . $name . '",
        `user_email` = "' . $email . '",
        `user_phone` = "' . $phone . '",
        ' . ($changePassword ? '`user_password` = "' . password_hash($password_confirmation, PASSWORD_BCRYPT, ['cost' => 12]) . '",' : '') . '
        `user_status` = "' . $status . '",
        `user_slug` = "' . $this->slugify($id . '-' . $name) . '"';
        
        $sql = 'UPDATE `users` SET ' . $values . ' WHERE `user_id` = ' . $id;

        $update_user = $this->db_update($sql);
        if ($update_user) {
            for ($i = 0; $i < count($permissions); $i++) {
                $permission_key = array_keys($permissions);
                $permission_data = (array) $permissions[$permission_key[$i]];

                for ($j = 0; $j < count($permission_data); $j++) {
                    $permission_data_key = array_keys($permission_data);
                    $permission_data_value = $permission_data[$permission_data_key[$j]] ? "true" : "false";

                    $permission = $permission_key[$i] . '.' . $permission_data_key[$j];
                    $sql = 'UPDATE `users_permissions` SET `user_permission_status` = "' . $permission_data_value . '" WHERE `user_id` = ' . $id . ' AND `user_permission_permission` = "' . $permission . '"';
                    $this->db_update($sql);
                }
            }

            if ($status == "false") {
                $sql = 'DELETE FROM `api_sessions` WHERE `user_id` = ' . $id;
                $this->db_update($sql);
            }

            $this->generate_user_log("users.update");
            return ['message' => "User updated successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error updating user"];
        }
    }

    public function delete(string $slug)
    {
        $sql = 'UPDATE `users` SET `user_is_deleted` = "true" WHERE `user_slug` = "' . $slug . '"';
        $delete_user = $this->db_delete($sql);
        if ($delete_user) {
            $this->generate_user_log("users.delete");
            return ['message' => "User deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting user"];
        }
    }

    protected function verify_exist_email(string $email, string $id = "")
    {
        $sql = 'SELECT `user_id` AS "id" FROM `users` WHERE `user_email` = "' . $email . '" AND `user_is_deleted` = "false"';
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
    }
}
