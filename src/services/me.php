<?php
class Me extends API_configuration
{
    private function generate_token(string $user_id)
    {
        $token = md5($user_id . uniqid(rand(), true));
        $expiration_date = date("Y-m-d H:i:s", strtotime("+10 hours"));
        $sql = 'SELECT `id` FROM `api_sessions` WHERE `user_id` = ' . $user_id;
        $get_user_token_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_token_data) > 0) {
            $sql = 'UPDATE `api_sessions` SET `token` = "' . $token . '",  `expires` = "' . $expiration_date . '" WHERE `user_id` = ' . $user_id;
        } else {
            $sql = 'INSERT INTO `api_sessions` (`user_id`, `token`, `expires`) VALUES (' . $user_id . ', "' . $token . '", "' . $expiration_date . '")';
        }
        $this->db_update($sql);
        return $token;
    }

    public function login(object $parms)
    {
        $email = $parms->email;
        $password = $parms->password;

        $sql = 'SELECT * FROM `users` WHERE `email` = "' . $email . '"';
        $get_user_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_data) == 1) {
            $user_data = $this->db_object($get_user_data);
            if (password_verify($password, $user_data->password)) {
                if ($user_data->status == 'false') {
                    http_response_code(401);
                    return [];
                }

                // get user permissions
                $sql = 'SELECT `permission` FROM `users_permissions` WHERE `user_id` = ' . $user_data->id . ' AND `status` = "true"';
                $get_user_permissions = $this->db_read($sql);
                $user_permissions = [];
                while ($user_permission = $this->db_object($get_user_permissions)) {
                    array_push($user_permissions, $user_permission->permission);
                }

                $this->generate_user_log("login", null, $user_data->id);
                
                return [
                    'user' => [
                        'id' => (int) $user_data->id,
                        'name' => $user_data->name,
                        'avatar' => $user_data->avatar,
                        'permissions' => $user_permissions,
                        'slug' => $user_data->slug
                    ],
                    'token' => $this->generate_token($user_data->id)
                ];
            }
        } else {
            http_response_code(401);
            return [];
        }
    }

    public function logout()
    {
        $token = $this->headers->token;

        $sql_token = str_replace("Bearer ", "", $token);

        $sql = 'SELECT `user_id` FROM `api_sessions` WHERE `token` = "' . $sql_token . '"';
        $get_user_token_data = $this->db_read($sql);
        $user_token_data = $this->db_object($get_user_token_data);

        $this->generate_user_log("logout", null, $user_token_data->user_id);

        $sql = 'DELETE FROM `api_sessions` WHERE `token` = "' . $sql_token . '"';
        $this->db_delete($sql);
        return [];
    }

    public function session(object $parms)
    {
        $email = $parms->email;
        $sql = 'SELECT `id`, `name`, `avatar`, `slug` FROM `users` WHERE `email` = "' . $email . '"';
        $get_user_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_data) > 0) {
            $user_data = $this->db_object($get_user_data);

            // get user permissions
            $sql = 'SELECT `permission` FROM `users_permissions` WHERE `user_id` = ' . $user_data->id . ' AND `status` = "true"';
            $get_user_permissions = $this->db_read($sql);
            $user_permissions = [];
            while ($user_permission = $this->db_object($get_user_permissions)) {
                array_push($user_permissions, $user_permission->permission);
            }

            // get user token
            $sql = 'SELECT `token` FROM `api_sessions` WHERE `user_id` = ' . $user_data->id;
            $get_user_token_data = $this->db_read($sql);
            $user_token_data = $this->db_object($get_user_token_data);

            return [
                'user' => [
                    'id' => (int) $user_data->id,
                    'name' => $user_data->name,
                    'avatar' => $user_data->avatar,
                    'permissions' => $user_permissions,
                    'slug' => $user_data->slug
                ],
                'token' => $user_token_data->token
            ];
        } else {
            return [];
        }
    }
}
