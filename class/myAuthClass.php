<?php
class myAuthClass
{
    public static function is_auth($current_session)
    {
        if (isset($current_session['user']) && !empty($current_session['user']))
            return true;
        return false;
    }

    public static function authenticate($username, $password)
    {
        $db = require(dirname(__FILE__) . '/../lib/mypdo.php');
        $fields = array(
            'id',
            'username',
            'admin',
        );
        $sql = 'SELECT ' . implode(', ', $fields) . ' ';
        $sql .= 'FROM utilisateurs ';
        $sql .= 'WHERE username = :username AND password = :passhash';
        $statement = $db->prepare($sql);
        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        $statement->bindValue(':passhash', md5($password), PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function checkPriviledgeDatabase(string $username)
    {
        try {
            $db = require(dirname(__FILE__) . '/../lib/mypdo.php');
            $sql = "SELECT admin
					FROM utilisateurs
					WHERE username = :username";
            $statement = $db->prepare($sql);
            $statement->bindValue(':username', $username, PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['admin'] : false;
        } catch (Error | Exception $e) {
            echo $e->getMessage() . ' -> file: ' . $e->getFile() . ' - ligne: ' . $e->getLine();
        }
    }
}
