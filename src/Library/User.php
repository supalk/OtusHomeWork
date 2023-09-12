<?php

namespace App\Library;

class User
{
    public ?int $user_id;
    public string $login;
    protected string $password;
    public string $name;
    public ?string $surname;
    public ?string $lastname;
    public ?int $gender;
    public ?string $biography;
    public ?string $city;

    public function getRequired()
    {
        return ["login","password","name"];
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = Auth::getHashPassword($password);
    }

    public function load(array $params)
    {
        foreach ($params as $field=>$v){
            if (property_exists($this, $field)){
                $this->$field = $v;
            }
        }
    }

    public function getArray($has_value = true): array
    {
        $data = [];
        foreach (get_class_vars(__CLASS__) as $name => $v) {
            if ($name == 'user_id') continue;
            if ($has_value)
                $data[$name] = $this->$name;
            else
                $data[] = $name;
        }

        return $data;
    }

}
