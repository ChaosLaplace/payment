<?php if ( !defined('XXOO') ) exit('No direct script access allowed');

/**
 * 数据库操作类
 * xxoo框架默认载入，无需手动引入
 * sql占位符
 *     ?s 字符串
 *     ?i 整数
 *     ?f 浮点数
 *     ?p sql片段
 *
 * @author marvin
 */
class DB {

    /**
     * 运行一条sql语句
     * @param $sql
     * @param array $binds
     * @param string $conf_name
     * @return mixed
     */
    public static function runSql($sql, $binds=array(), $conf_name='') {
        return self::run($sql, $binds, $conf_name);
    }

    /**
     * 获取一条记录
     * @param $sql
     * @param array $binds
     * @param string $conf_name
     * @return bool
     */
    public static function getLine($sql, $binds=array(), $conf_name='') {
        $rs = self::run($sql, $binds, $conf_name);
        return empty($rs) ? false : $rs[0];
    }

    /**
     * 获取多条记录
     * @param $sql
     * @param array $binds
     * @param string $conf_name
     * @return bool|mixed
     */
    public static function getData($sql, $binds=array(), $conf_name='') {
        $rs = self::run($sql, $binds, $conf_name);
        return empty($rs) ? false : $rs;
    }

    /**
     * 获取一个值
     * @param $sql
     * @param array $binds
     * @param string $conf_name
     * @return bool|mixed
     */
    public static function getVar($sql, $binds=array(), $conf_name='') {
        $rs = self::run($sql, $binds, $conf_name);

        if( !empty($rs) && !empty($rs[0]) ) {
            return is_array($rs[0]) ? @array_shift($rs[0]) : false;
        }
        return false;
    }

    /**
     * 插入一条记录
     * @param $table
     * @param $data
     * @param string $conf_name
     * @return mixed
     */
    public static function insert($table, $data, $conf_name='') {
        $fields = $values = $binds  = array();

        foreach($data as $field=>$value) {
            $fields[] = "`{$field}`";

            if( is_int($value) ) {
                $values[] = '?i';
            }
            else if( is_float($value) ) {
                $values[] = '?f';
            }
            else {
                $values[] = '?s';
            }

            $binds[] = $value;
        }
        $fields = implode(', ', $fields);
        $values = implode(', ', $values);

        $sql = "INSERT INTO `{$table}` ({$fields}) values ({$values})";

        return self::run($sql, $binds, $conf_name);
    }

    /**
     * 插入多条记录
     * @param $table
     * @param $data
     * @param string $conf_name
     * @return mixed
     */
    public static function inserts($table, $data, $conf_name='') {
        $field_arr = array();
        $value_sql_arr = array();

        $first = true;
        foreach($data as $d) {
            $value_arr = array();
            foreach($d as $field=>$value ) {
                if($first) {
                    $field_arr[] = $field;
                }

                if( is_string($value) ) {
                    $value = addslashes($value);
                    $value_arr[] = "'{$value}'";
                }
                else {
                    $value_arr[] = $value;
                }
            }
            $value_sql_arr[] = '(' . implode( ',', $value_arr ) . ')';
            $first = false;
        }

        $fields_sql = '`' . implode( '`, `', $field_arr ) . '`';
        $values_sql = implode( ',', $value_sql_arr );

        $sql = "INSERT INTO `{$table}` ({$fields_sql}) VALUES {$values_sql}";

        return self::run($sql, null, $conf_name);
    }

    /**
     * 修改记录
     * @param $table
     * @param $data
     * @param string $wheres
     * @param string $conf_name
     * @return mixed
     */
    public static function update($table, $data, $wheres='', $conf_name='') {
        $binds  = array();
        $sets   = array();
        foreach($data as $field=>$value) {
            if( is_int($value) ) {
                $typ = '?i';
            }
            else if( is_float($value) ) {
                $typ = '?f';
            }
            else {
                $typ = '?s';
            }

            $sets[] = "`{$field}`={$typ}";

            $binds[] = $value;
        }
        $sets_sql = implode(', ', $sets);

        $where_sql = '';
        if( !empty($wheres) ) {
            if( is_array($wheres) ) {
                $conds = array();
                foreach($wheres as $field=>$value) {
                    if( is_int($value) ) {
                        $typ = '?i';
                    }
                    else if( is_float($value) ) {
                        $typ = '?f';
                    }
                    else {
                        $typ = '?s';
                    }

                    $conds[] = "`{$field}`={$typ}";

                    $binds[] = $value;
                }
                $where_sql = 'WHERE ' . implode(' AND ', $conds);
            }
            else {
                $where_sql = 'WHERE ' . $wheres;
            }
        }

        $sql = "UPDATE `{$table}` SET {$sets_sql} {$where_sql}";

        return self::run($sql, $binds, $conf_name);
    }

    /**
     * 事务操作
     * @param $act
     * @param string $conf_name
     * @return bool
     */
    public static function trans($act, $conf_name='') {
        static $trans_times = 0;
        $act = strtoupper( $act );
        $act_list = array('BEGIN', 'COMMIT', 'ROLLBACK');

        if( !in_array( $act, $act_list ) ) {
            trigger_error("Unknow transation action: {$act}");
        }

        $conn = self::getConnect($conf_name);

        // 开始事务
        if( $act == 'BEGIN' ) {
            if( $trans_times == 0 ) {
                $conn->beginTrans();
            }
            $trans_times++;
            return true;
        }

        // 提交事务
        if( $act == 'COMMIT' ) {
            if( $trans_times == 1 ) {
                $conn->commit();
            }
            else {
                $trans_times--;
            }
            return true;
        }

        // 回滚事务
        if( $act == 'ROLLBACK' ) {
            if( $trans_times > 0 ) {
                $conn->rollback();
            }
            return true;
        }
    }

    public static function lock() {

    }

    public static function unlock() {

    }

    /**
     * 执行sql
     * @param $sql
     * @param array $binds
     * @param string $conf_name
     * @return mixed
     */
    private static function run($sql, $binds=array(), $conf_name='') {
        // 替换sql占位符
        // 整数:?i，浮点数:?f，字符串:?s，sql片段:?p(即直接替换为字符串不添加引号)
        if( preg_match("/\?i|\?s|\?f|\?p/", $sql) && !empty($binds) ) {
            $sql = str_replace(['?i', '?f', '?s', '?p'], ['%d', '%f', "'%s'", '%s'], $sql);

            // 字符串引号转义
            foreach($binds as $k=>$v) {
                if( is_string($v) ) {
                    $binds[$k] = addslashes($v);
                }
            }
            $sql = vsprintf($sql, $binds);
        }

        // 为了方便调试，在开发状态下，打印出可执行sql语句
        // 注意：不代表数据库已执行
        if( $GLOBALS['app']['env'] == 'development' ) {
            Log::debug('run sql: ' . $sql);
        }

        $conn = self::getConnect($conf_name);
        return $conn->run($sql);
    }

    /**
     * 关闭数据库连接
     * @param string $conf_name
     */
    public static function close($conf_name='') {
        // 如果数据库连接存在，则关闭
        $connect_name = self::connectName($conf_name);
        if( isset($GLOBALS[$connect_name]) ) {
            $conn = self::getConnect($conf_name);
            $conn->close();
        }
    }

    /**
     * 获取数据库连接
     * @param string $conf_name
     * @return mixed
     */
    private static function getConnect($conf_name='') {
        $conf_name = empty($conf_name) ? 'default' : $conf_name;

        $connect_name = self::connectName($conf_name);
        if( isset($GLOBALS[$connect_name]) ) {
            return $GLOBALS[$connect_name];
        }

        $GLOBALS[$connect_name] = new XPDO($GLOBALS['db'][$conf_name]);
        return $GLOBALS[$connect_name];
    }

    /**
     * 根据数据库配置，生成数据库连接名称
     * @param $conf_name
     * @return string
     */
    private static function connectName($conf_name) {
        $conf_name = empty($conf_name) ? 'default' : $conf_name;

        $conf = $GLOBALS['db'][$conf_name];
        if ( !$conf ) {
            trigger_error('Can\'t find $GLOBALS[\'db\'][\''.$conf_name.'\'] - in database.conf.php');
        }

        ksort($conf);   // 按字典升序排序
        return implode('-', $conf);
    }

}

class XPDO {

    private $conf;
    private $conn;
    private $lastRunTime;

    public function __construct($conf) {
        $this->conf = $conf;
        $this->connect();
        $this->lastRunTime = time();
    }

    public function run($sql) {
        // 每半小时重连一次，避免mysql超时
        $time = time();
        if( $time - $this->lastRunTime > 30*60 ) {
            $this->connect();
            $this->lastRunTime = $time;
        }

        if( preg_match('/^select/i', $sql) ) {
            $smt = $this->conn->query($sql);
            $rs = $smt->fetchAll(PDO::FETCH_ASSOC);
        }
        else if( preg_match('/^insert/i', $sql) ) {
            $this->conn->exec($sql);
            $rs = $this->conn->lastInsertId();
        }
        else {
            $rs = $this->conn->exec($sql);
        }

        return $rs;
    }

    public function beginTrans() {
        $this->conn->beginTransaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollBack() {
        $this->conn->rollBack();
    }

    public function close() {
        $this->conn = null;
    }

    private function connect() {
        // 根据数据库driver生成对应的dsn
        if( $this->conf['driver'] == 'oracle') {
            $dsn = "oci:host={$this->conf['hostname']};dbname=ORCL;prot={$this->conf['port']}";
        }
        else {
            $dsn = "mysql:dbname={$this->conf['database']};host={$this->conf['hostname']}:{$this->conf['port']}";
        }

        try {
            $this->conn = new PDO($dsn,$this->conf['username'], $this->conf['password'],
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_STRINGIFY_FETCHES => false, //设置不强转
                    PDO::ATTR_EMULATE_PREPARES => false, //禁用预处理
                ]
            );
        }
        catch(PDOException $e) {
            Log::error($e->getMessage());
            trigger_error('Connect database fail:' . $e->getMessage(), E_USER_ERROR);
        }
    }

}
