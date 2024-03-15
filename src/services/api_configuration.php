<?php
class API_configuration
{
    private $connection;
    private string $api_token;
    protected string $today;
    protected string $now;
    protected string $base_url_image;
    public string $token = "";
    public object $get;
    public object $headers;

    function __construct()
    {
        if (SANDBOX) {
            $server = "localhost";
            $user = "root";
            $password = "";
            $db_name = "u524077001_mf_consultoria";
            $api_token = "SECRET_KEY";
            $connection = mysqli_connect($server, $user, $password, $db_name);
            mysqli_set_charset($connection, "utf8");
        } else {
            $server = "localhost";
            $db_name = "u524077001_mf_consultoria";
            $user = "u524077001_mf_consultoria";
            $password = "";
            $api_token = "";
            $connection = mysqli_connect($server, $user, $password, $db_name);
        }

        $protocol = 


        $this->api_token = $api_token;
        $this->connection = $connection;
        $this->today = date("Y-m-d");
        $this->now = date("Y-m-d H:i:s");
        $this->base_url_image = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/public/images/";
        $this->get = (object) $_GET;
        $this->headers = (object) getallheaders();
    }

    protected function db_create($sql)
    {
        try {
            mysqli_query($this->connection, $sql);
            return mysqli_insert_id($this->connection);
        } catch (Exception $e) {
            $this->errorMessage($sql);
            return false;
        }
    }

    protected function db_update($sql)
    {
        try {
            mysqli_query($this->connection, $sql);
            return true;
        } catch (Exception $e) {
            $this->errorMessage($sql);
            return false;
        }
    }

    protected function db_delete($sql)
    {
        try {
            mysqli_query($this->connection, $sql);
            return true;
        } catch (Exception $e) {
            $this->errorMessage($sql);
            return false;
        }
    }

    protected function db_read($sql)
    {
        $query = mysqli_query($this->connection, $sql);
        return $query;
    }

    protected function db_set($sql)
    {
        try {
            mysqli_query($this->connection, $sql);
            return true;
        } catch (Exception $e) {
            $this->errorMessage($sql);
            return false;
        }
    }

    protected function db_object($query_result)
    {
        return mysqli_fetch_object($query_result);
    }

    protected function db_array($query_result)
    {
        return mysqli_fetch_array($query_result);
    }

    protected function db_assoc($query_result)
    {
        return mysqli_fetch_assoc($query_result);
    }

    protected function db_num_rows($query_result)
    {
        return mysqli_num_rows($query_result);
    }

    protected function slugify($string)
    {
        $string = preg_replace('/[\t\n]/', ' ', $string);
        $string = preg_replace('/\s{2,}/', ' ', $string);
        $list = array(
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            '&' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            '&' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ŕ' => 'R',
            'ŕ' => 'r',
            '/' => '-',
            ' ' => '-',
            '(' => '',
            ')' => '',
            '.' => '',
        );
        $string = strtr($string, $list);
        $string = preg_replace('/-{2,}/', '-', $string);
        $string = strtolower($string);
        return $string;
    }

    protected function upload_image(string $image, string $name)
    {
        $path = "public/images/" . $name;
        $this->base64_to_jpeg($image, $path);
        return $name;
    }

    protected function delete_image(string $name)
    {
        $path = "public/images/" . $name;
        if (file_exists($path)) {
            unlink($path);
            return true;
        } else {
            return false;
        }
    }

    private function base64_to_jpeg($base64_string, $output_file)
    {
        $ifp = fopen($output_file, 'wb');
        $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);
        return $output_file;
    }

    protected function real_to_float($value)
    {
        $num = str_replace('R$', '', $value);
        $num = str_replace(' ', '', $num);
        $num = str_replace('.', '', $num);
        $num = str_replace(',', '.', $num);
        return floatval($num);
    }

    public function authorization(string $type = "user")
    {
        if ($type == "user") {
            $sql_token = str_replace("Bearer ", "", $this->token);
            $sql = 'SELECT `user_id`, `api_session_expires` AS "expires" FROM `api_sessions` WHERE `api_session_token` = "' . addslashes($sql_token) . '"';
            $get_user_token_data = $this->db_read($sql);
            if ($this->db_num_rows($get_user_token_data) > 0) {
                $user_token_data = $this->db_object($get_user_token_data);
                if (strtotime($user_token_data->expires) > strtotime($this->now)) {
                    return $user_token_data->user_id;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else if ($type == "api") {
            if ($this->token == $this->api_token) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function generate_user_log(
        string $action,
        string $description = null,
        int $user_id = null
    ) {
        $sql = 'SELECT `user_log_id` AS "id", `user_log_action` AS "action" FROM `users_logs` WHERE `user_id`=' . ($user_id != null ? $user_id : $_SESSION['user_id']) . ' ORDER BY `user_log_id` DESC LIMIT 1';
        $get_last_log = $this->db_read($sql);
        if ($this->db_num_rows($get_last_log) > 0) {
            $last_log = $this->db_object($get_last_log);
            if ($last_log->action != $action) {
                $sql = 'INSERT INTO `users_logs` (`user_id`, `user_log_date`, `user_log_action`' . ($description != null ? ', `user_log_description`' : '') . ') VALUES ("' . ($user_id != null ? $user_id : $_SESSION['user_id']) . '", "' . date('Y-m-d H:i:s') . '", "' . addslashes($action) . '"' . ($description != null ? ', "' . addslashes($description) . '"' : '') . ')';
                $this->db_create($sql);
            }
        } else {
            $sql = 'INSERT INTO `users_logs` (`user_id`, `user_log_date`, `user_log_action`' . ($description != null ? ', `user_log_description`' : '') . ') VALUES ("' . ($user_id != null ? $user_id : $_SESSION['user_id']) . '", "' . date('Y-m-d H:i:s') . '", "' . addslashes($action) . '"' . ($description != null ? ', "' . addslashes($description) . '"' : '') . ')';
            $this->db_create($sql);
        }
    }

    private function errorMessage(string $sql)
    {
        if (SANDBOX) {
            header("Content-Type: html/text");
            http_response_code(500);
            echo '
            <h1>Erro ao executar o SQL</h1>
            <p><b>SQL:</b> ' . $sql . '</p>';
            throw new Exception("Erro ao executar o SQL: " . $sql);
        } else {
            throw new Exception("Internal Server Error");
        }
    }
}
