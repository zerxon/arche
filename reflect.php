<?php

class User {

    /** filed = id */
    private $id;

    /** Column(name="user_name") */
    private $userName;

    /** filed = pwd */
    private $password;

    public function setUserName($name) {
        $this->userName = $name;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function __get($var) {
        return $this->var;
    }

    public function __set($var, $value) {
        $this->var = $value;
    }

    public function __call($method, $args) {
        $type = substr($method,0,3);
        $property = str_replace($type, '', $method);
        $property = strtolower(substr($property,0,1)).substr($property,1,strlen($property) - 1);

        if(!property_exists($this,$property)) {
            echo $property." 未定义";
            return;
        }

        if($type == 'get') {
            return $this->$property;
        }
        elseif($type == 'set') {
            $this->$property = $args[0];
        }
    }
 }

$user = new User();
$user->setPassword('abc');
echo $user->getPassword();

/*
$class = new ReflectionClass('User');

$properties = $class->getProperties(ReflectionProperty::IS_PRIVATE);

foreach($properties as $key => $property) {
    //echo $property->getName()."\n";
    preg_match('/ filed \= ([a-z_]*) /', $property->getDocComment(), $matches);
    //preg_match('/Column\(name=\"([a-z_]*)\"\)/', $property->getDocComment(), $matches);

    echo $matches[1]."\n";
}


$instance = $class->newInstance();
$class->hasMethod('setUserName');

$ec=$class->getmethod('setUserName');
$ec->invoke($instance,'wallace');

echo $instance->getUserName();
*/