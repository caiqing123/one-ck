<?php

namespace OneCk;

class Write
{
    /**
     * @var resource
     */
    private $conn;

    private $buf = '';

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function int($n)
    {
        $this->buf .= pack('l', $n);
        return $this;
    }

    /**
     * @param int ...$str
     * @return $this
     */
    public function string($str)
    {
        foreach ($str as $s) {
            $this->number([strlen($s)]);
            $this->buf .= $s;
        }
        return $this;
    }

    /**
     * @param int ...$nr
     * @return $this
     */
    public function number($nr)
    {
        $r = ['C*'];
        foreach ($nr as $k => $n) {
            $b = 0;
            while ($n >= 128) {
                $r[] = $n | 128;
                $b++;
                $n = $n >> 7;
            }
            $r[] = $n;
        }
        $this->buf .= call_user_func_array("pack", $r);
        return $this;
    }

    /**
     * @param $str
     * @return $this
     */
    public function addBuf($str)
    {
        $this->buf .= $str;
        return $this;
    }


    public function flush()
    {
        if ($this->buf === '') {
            return true;
        }
        $len = fwrite($this->conn, $this->buf);
        if ($len !== strlen($this->buf)) {
            throw new CkException('write fail', CkException::CODE_WRITE_FAIL);
        }
        $this->buf = '';
        return true;
    }

}