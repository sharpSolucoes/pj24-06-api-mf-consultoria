<?php
class Me_Client extends API_configuration
{
    private function generate_token(string $user_id, string $type)
    {
        $token = md5($user_id . uniqid(rand(), true));
        $expiration_date = date("Y-m-d H:i:s", strtotime("+10 hours"));
        $sql = 'SELECT `api_session_client_id` AS "id" FROM `api_sessions_clients` WHERE ' . ($type == 'client' ? '`client_id`' : '`client_user_id`') . ' = ' . $user_id;
        $get_user_token_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_token_data) > 0) {
            $user_token_data = $this->db_object($get_user_token_data);
            $sql = 'UPDATE `api_sessions_clients` SET `api_session_client_token` = "' . $token . '",  `api_session_client_expires` = "' . $expiration_date . '" WHERE `api_session_client_id` = ' . $user_token_data->id;
        } else {
            if ($type == 'client') {
                $sql = 'INSERT INTO `api_sessions_clients` (`client_id`, `api_session_client_token`, `api_session_client_expires`) VALUES (' . $user_id . ', "' . $token . '", "' . $expiration_date . '")';
            } else {
                $sql = 'INSERT INTO `api_sessions_clients` (`client_user_id`, `api_session_client_token`, `api_session_client_expires`) VALUES (' . $user_id . ', "' . $token . '", "' . $expiration_date . '")';
            }
        }
        $this->db_update($sql);
        return $token;
    }

    public function login()
    {
        $email = $this->headers->email;
        $image = $this->headers->image;

        $sql = 'SELECT * FROM `clients_emails` WHERE `client_email_email` = "' . $email . '"';
        $get_user_email_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_email_data) == 1) {
            $user_email_data = $this->db_object($get_user_email_data);

            if ($user_email_data->client_id != NULL) {
                $sql = 'SELECT `client_status` AS "user_status" FROM `clients` WHERE `client_id` = ' . $user_email_data->client_id;
                $get_user_data = $this->db_read($sql);
                $user_data = $this->db_object($get_user_data);
            } else {
                $sql = 'SELECT `client_user_status` AS "user_status" FROM `clients_users` WHERE `client_user_id` = ' . $user_email_data->client_user_id;
                $get_user_data = $this->db_read($sql);
                $user_data = $this->db_object($get_user_data);

                $sql = "UPDATE `clients_users` SET `client_user_avatar` = '" . $image . "' WHERE `client_user_id` = " . $user_email_data->client_user_id;
                $this->db_update($sql);
            }

            if ($user_data->user_status == 'false') {
                http_response_code(401);
            } else {
                $this->generate_token(($user_email_data->client_id != NULL ? $user_email_data->client_id : $user_email_data->client_user_id), ($user_email_data->client_id != NULL ? 'client' : 'client_user'));
                http_response_code(200);
            }
        } else {
            http_response_code(401);
        }
    }

    public function logout()
    {
        $token = $this->headers->token;

        $sql_token = str_replace("Bearer ", "", $token);

        $sql = 'DELETE FROM `api_sessions_clients` WHERE `api_session_client_token` = "' . $sql_token . '"';
        $this->db_delete($sql);
        return [];
    }

    public function session()
    {
        $email = $this->headers->email;
        $sql = 'SELECT * FROM `clients_emails` WHERE `client_email_email` = "' . $email . '"';
        $get_user_email_data = $this->db_read($sql);
        if ($this->db_num_rows($get_user_email_data) == 1) {
            $user_email_data = $this->db_object($get_user_email_data);

            if ($user_email_data->client_id != NULL) {
                $sql = 'SELECT `client_id` AS "id", `client_name` AS "name", `client_logo` AS "avatar" FROM `clients` WHERE `client_id` = ' . $user_email_data->client_id;
                $get_user_data = $this->db_read($sql);
                $user_data = $this->db_object($get_user_data);

                $user_data->avatar = $user_data->avatar != '' ? $this->base_url_image . $user_data->avatar : null;
                $user_data->type = 'client';
                $user_data->position = 'Empresa';
                $user_data->email = $email;
            } else {
                $sql = 'SELECT `client_user_id` AS "id", `client_user_name` AS "name", `client_user_avatar` AS "avatar", `client_user_position` AS "position", `client_id` AS "clientId" FROM `clients_users` WHERE `client_user_id` = ' . $user_email_data->client_user_id;
                $get_user_data = $this->db_read($sql);
                $user_data = $this->db_object($get_user_data);

                $user_data->type = 'client_user';
                $user_data->email = $email;
            }

            // get user token
            $sql = 'SELECT `api_session_client_token` AS "token" FROM `api_sessions_clients` WHERE ' . ($user_email_data->client_id != NULL ? '`client_id`' : '`client_user_id`') . ' = ' . ($user_email_data->client_id != NULL ? $user_email_data->client_id : $user_email_data->client_user_id);
            $get_user_token_data = $this->db_read($sql);
            $user_token_data = $this->db_object(
                $get_user_token_data
            );

            $user = [
                'id' => (int) $user_data->id,
                'name' => $user_data->name,
                'avatar' => $user_data->avatar,
                'position' => $user_data->position,
                'type' => $user_data->type
            ];
            
            if (isset($user_data->clientId)) {
                $user['clientId'] = $user_data->clientId;
            }
            
            return [
                'user' => $user,
                'token' => $user_token_data->token
            ];
        } else {
            return [];
        }
    }
}
