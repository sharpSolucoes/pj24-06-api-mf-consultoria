<?php
class Clients_Emails extends API_configuration
{
    public function create(string $type, int $id, string $email)
    {
        $values = '
        "' . $id . '",
        "' . $email . '"
      ';

        if ($type == "client") {
            $sql = 'INSERT INTO `clients_emails` (`client_id`, `client_email_email`) VALUES (' . $values . ')';
        } else if ($type == "client_user") {
            $sql = 'INSERT INTO `clients_emails` (`client_user_id`, `client_email_email`) VALUES (' . $values . ')';
        }
        $create_client_email = $this->db_create($sql);

        $this->generate_user_log("client.create_email");
    }

    public function read_by_email(
        string $email
    ) {
        $sql = "SELECT `client_id`, `client_user_id` FROM `clients_emails` WHERE `client_email_email` = '" . $email . "' AND `client_email_is_deleted` = 'false'";
        $get_email = $this->db_read($sql);
        if ($this->db_num_rows($get_email) > 0) {
            $email = $this->db_object($get_email);
            return $email;
        } else {
            return false;
        }
    }

    public function update(string $type, int $id, string $email)
    {
        $values = '
            `client_email_email` = "' . $email . '"';
        if ($type == "client") {
            $sql = 'UPDATE `clients_emails` SET ' . $values . ' WHERE `client_id` = ' . $id;
        } else if ($type == "client_user") {
            $sql = 'UPDATE `clients_emails` SET ' . $values . ' WHERE `client_user_id` = ' . $id;
        }

        $update_client_email = $this->db_update($sql);

        $this->generate_user_log("client.update_email");
    }

    public function delete(string $email)
    {
        $sql = 'UPDATE `clients_emails` SET `client_email_is_deleted` = "true" WHERE `client_email_email` = "' . $email . '"';
        
        $delete_email = $this->db_update($sql);

        $this->generate_user_log("client.delete_email");
    }
}
