<?php
class Clients extends API_configuration
{
    private $users;
    private $reports;

    function __construct()
    {
        parent::__construct();
        $this->users = new Clients_Users();
        $this->reports = new Clients_Reports();
    }

    public function create(object $parms)
    {
        $logo = $parms->logo;

        $name = $parms->name;
        $cnpj = $parms->cnpj;
        $email = $parms->email;
        $phone = $parms->phone;
        $status = $parms->status;
        $address = (array) $parms->address;

        if ($this->verify_exist_email($email)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        "' . $name . '",
        "' . $cnpj . '",
        "' . $email . '",
        "' . $phone . '",
        "' . $status . '",
        "' . $address['street'] . '",
        "' . $address['number'] . '",
        "' . $address['complement'] . '",
        "' . $address['neighborhood'] . '",
        "' . $address['city'] . '",
        "' . $address['state'] . '",
        "' . $address['zipCode'] . '"';

        $sql = 'INSERT INTO `clients` (`name`, `cnpj`, `email`, `phone`, `status`, `street`, `number`, `complement`, `neighborhood`, `city`, `state`, `zip`) VALUES (' . $values . ')';
        $create_client = $this->db_create($sql);
        if ($create_client) {
            $client_id = $create_client;

            $slug = $this->slugify($client_id . '-' . $name);
            $sql = 'UPDATE `clients` SET `slug` = "' . $slug . '" WHERE `id` = ' . $client_id;
            $this->db_update($sql);

            if ($logo) {
                $file_extension = pathinfo($logo->name, PATHINFO_EXTENSION);
                $file_base64_content = $logo->content;

                $file_new_name = $client_id . '-' . time() . '.' . $file_extension;
                $path_logo = $this->upload_image($file_base64_content, $file_new_name);

                $sql = 'UPDATE `clients` SET `logo` = "' . $path_logo . '" WHERE `id` = ' . $client_id;
                $this->db_update($sql);
            }

            $this->generate_user_log("clients.create");
            return ['message' => "Client created successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error creating user"];
        }
    }

    public function read()
    {
        $sql = 'SELECT `id`, `status`, `name`, `cnpj`, `phone`, `street`, `number`, `complement`, `neighborhood`, `city`, `state`, `zip`, `slug` FROM `clients` WHERE `is_deleted` = "false"';
        $get_clients_data = $this->db_read($sql);
        if ($this->db_num_rows($get_clients_data) > 0) {
            $clients_data = [];
            while ($client_data = $this->db_object($get_clients_data)) {
                $client_data->id = (int) $client_data->id;
                $client_data->status = $client_data->status == 'true' ? true : false;
                $client_data->name = mb_convert_case($client_data->name, MB_CASE_TITLE, 'UTF-8');
                $client_data->address = mb_convert_case($client_data->street . ', ' . $client_data->number . ', ' . ($client_data->complement != '' ? $client_data->complement . ', ' : '') . $client_data->neighborhood . ', ' . $client_data->city . '/', MB_CASE_TITLE, 'UTF-8') . $client_data->state;

                unset($client_data->street);
                unset($client_data->number);
                unset($client_data->complement);
                unset($client_data->neighborhood);
                unset($client_data->city);
                unset($client_data->state);
                unset($client_data->zip);

                array_push($clients_data, $client_data);
            }

            $this->generate_user_log("clients.read");
            return $clients_data;
        } else {
            return [];
        }
    }

    public function read_by_slug(string $slug)
    {
        $sql = 'SELECT `id`, `status`, `name`, `logo`, `cnpj`, `email`, `phone`, `street`, `number`, `complement`, `neighborhood`, `city`, `state`, `zip` FROM `clients` WHERE `slug` = "' . $slug . '"';
        $get_client_data = $this->db_read($sql);
        if ($this->db_num_rows($get_client_data) > 0) {
            $client_data = $this->db_object($get_client_data);
            $client_data->id = (int) $client_data->id;
            $client_data->name = mb_convert_case($client_data->name, MB_CASE_TITLE, 'UTF-8');
            $client_data->address = [
                'street' => $client_data->street,
                'number' => $client_data->number,
                'complement' => $client_data->complement,
                'neighborhood' => $client_data->neighborhood,
                'city' => $client_data->city,
                'state' => $client_data->state,
                'zipCode' => $client_data->zip
            ];

            unset($client_data->street);
            unset($client_data->number);
            unset($client_data->complement);
            unset($client_data->neighborhood);
            unset($client_data->city);
            unset($client_data->state);
            unset($client_data->zip);

            $client_data->logo = $client_data->logo != '' ? "http://" . $_SERVER['HTTP_HOST'] . "/public/images/" . $client_data->logo : null;

            $client_data->users = $this->users->read_by_client_id($client_data->id);
            $client_data->reports = $this->reports->read_by_client_id($client_data->id);

            $this->generate_user_log("clients.read_by_slug");
            return $client_data;
        } else {
            http_response_code(404);
            return ['message' => "Client not found"];
        }
    }

    public function update(object $parms)
    {
        $id = $parms->id;
        $name = $parms->name;
        $cnpj = $parms->cnpj;
        $status = $parms->status;
        $email = $parms->email;
        $phone = $parms->phone;
        $address = (array) $parms->address;
        $changeLogo = $parms->changeLogo;

        if ($this->verify_exist_email($email, $id)) {
            http_response_code(409);
            return ['message' => "Email already exists"];
        }

        $values = '
        `name` = "' . $name . '",
        `cnpj` = "' . $cnpj . '",
        `email` = "' . $email . '",
        `phone` = "' . $phone . '",
        `status` = "' . $status . '",
        `street` = "' . $address['street'] . '",
        `number` = "' . $address['number'] . '",
        `complement` = "' . $address['complement'] . '",
        `neighborhood` = "' . $address['neighborhood'] . '",
        `city` = "' . $address['city'] . '",
        `state` = "' . $address['state'] . '",
        `zip` = "' . $address['zipCode'] . '"';

        $sql = 'UPDATE `clients` SET ' . $values . ' WHERE `id` = ' . $id;

        $update_client = $this->db_update($sql);
        if ($update_client) {
            if ($changeLogo) {
                $new_logo = $parms->newLogo;

                $sql = 'SELECT `logo` FROM `clients` WHERE `id` = ' . $id;
                $get_logo = $this->db_read($sql);
                $logo_data = $this->db_object($get_logo);
                if ($logo_data->logo != '') {
                    $this->delete_image($logo_data->logo);
                }

                $file_extension = pathinfo($new_logo->name, PATHINFO_EXTENSION);
                $file_base64_content = $new_logo->content;

                $file_new_name = $id . '-' . time() . '.' . $file_extension;
                $path_logo = $this->upload_image($file_base64_content, $file_new_name);

                $sql = 'UPDATE `clients` SET `logo` = "' . $path_logo . '" WHERE `id` = ' . $id;
                $this->db_update($sql);
            }

            if ($status == "false") {
                $sql = 'UPDATE `clients_users` SET `status` = "false" WHERE `client_id` = ' . $id;
                $this->db_update($sql);
            }

            $this->generate_user_log("clients.update");
            return ['message' => "Client updated successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error updating client"];
        }
    }

    public function delete(string $slug)
    {
        $sql = 'UPDATE `clients` SET `is_deleted` = "true", `status` = "false" WHERE `slug` = "' . $slug . '"';
        $delete_client = $this->db_delete($sql);
        if ($delete_client) {
            $sql = "UPDATE `clients_users` SET `is_deleted` = 'true', `status` = 'false' WHERE `client_id` = " . $delete_client;
            $this->db_delete($sql);

            $sql = "UPDATE `clients_reports` SET `is_deleted` = 'true' WHERE `client_id` = " . $delete_client;
            $this->db_delete($sql);

            $this->generate_user_log("clients.delete");
            return ['message' => "Client deleted successfully"];
        } else {
            http_response_code(400);
            return ['message' => "Error deleting client"];
        }
    }

    protected function verify_exist_email(string $email, string $id = "")
    {
        $sql = 'SELECT `id` FROM `clients_users` WHERE `email` = "' . $email . '" AND `is_deleted` = "false"';
        $get_user_email = $this->db_read($sql);
        if ($this->db_num_rows($get_user_email) == 0) {
            $sql = 'SELECT `id` FROM `clients` WHERE `email` = "' . $email . '" AND `is_deleted` = "false"';
            $get_client_email = $this->db_read($sql);
            if ($this->db_num_rows($get_client_email) > 0) {
                $client_data = $this->db_object($get_client_email);
                if ($client_data->id == $id) {
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
