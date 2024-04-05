<?php
namespace Zf\Ext\Model;

abstract class ZFModelEntity {
    public function __set($name, $value) {
        $method_name = 'set' . ucfirst ( $name );
        if (method_exists ( $this, $method_name )) {
            $this->$method_name ( $value );
        } else {
            $this->$name = $value;
        }
        return $this;
    }

    public function __get($name) {
        $method_name = 'get' . ucfirst ( $name );
        if (method_exists ( $this, $method_name )) {
            return $this->$method_name ();
        } else {
            return $this->$name;
        }
    }

    /**
     * Ham tra ve gia tri cua Entity duoi dang array
     *
     * @return array
     */
    public function toArray() {
        $result = array ();
        foreach ( get_object_vars ( $this ) as $key => $value ) {
            $result [$key] = $this->__get ( $key );
        }
        return $result;
    }

    /**
     * Ham thiet lap gia tri cho Entity
     *
     * @param array $options
     */
    public function fromArray($options = array()) {
        foreach ( $options as $key => $value ) {
            $this->__set ( $key, $value );
        }

        return $this;
    }

    /**
     * Ham khoi tao, ho tro cho cac class con
     */
    public function init() {
    }
    public function __construct($options) {
        if (is_array ( $options )) {
            $this->fromArray ( $options );
        }
        $this->init ();
    }
}

?>