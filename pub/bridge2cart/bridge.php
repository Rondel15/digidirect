<?php
interface M1_Platform_Actions
{
    /**
     * @param array $a2cData
     *
     * @return mixed
     */
    public function productUpdateAction(array $a2cData);

    /**
     * @param array $a2cData
     *
     * @return mixed
     */
    public function sendEmailNotifications(array $a2cData);

    /**
     * @return mixed
     */
    public function getPlugins();

    /**
     * @param array $a2cData
     *
     * @return mixed
     */
    public function triggerEvents(array $a2cData);

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function setMetaData(array $a2cData);


    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function getTranslations(array $a2cData);

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function setOrderNotes(array $a2cData);

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function getActiveModules(array $a2cData);

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function getImagesUrls(array $a2cData);

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function orderUpdate(array $a2cData);

}

abstract class M1_DatabaseLink
{
    protected static $_maxRetriesToConnect = 5;
    protected static $_sleepBetweenAttempts = 2;

    protected $_config = null;
    private $_databaseHandle = null;

    protected $_insertedId = 0;
    protected $_affectedRows = 0;

    /**
     * @param M1_Config_Adapter $config Config adapter
     * @return M1_DatabaseLink
     */
    public function __construct($config)
    {
        $this->_config = $config;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->_releaseHandle();
    }

    /**
     * @return stdClass|bool
     */
    private function _tryToConnect()
    {
        $triesCount = self::$_maxRetriesToConnect;

        $link = null;

        while (!$link) {
            if (!$triesCount--) {
                break;
            }
            $link = $this->_connect();
            if (!$link) {
                sleep(self::$_sleepBetweenAttempts);
            }
        }

        if ($link) {
            $this->_afterConnect($link);
            return $link;
        } else {
            return false;
        }
    }

    /**
     * Database handle getter
     * @return stdClass
     */
    protected final function _getDatabaseHandle()
    {
        if ($this->_databaseHandle) {
            return $this->_databaseHandle;
        }
        if ($this->_databaseHandle = $this->_tryToConnect()) {
            return $this->_databaseHandle;
        } else {
            exit($this->_errorMsg('Can not connect to DB'));
        }
    }

    /**
     * Close DB handle and set it to null; used in reconnect attempts
     * @return void
     */
    protected final function _releaseHandle()
    {
        if ($this->_databaseHandle) {
            $this->_closeHandle($this->_databaseHandle);
        }
        $this->_databaseHandle = null;
    }

    /**
     * Format error message
     * @param string $error Raw error message
     * @return string
     */
    protected final function _errorMsg($error)
    {
        $className = get_class($this);
        return "[$className] MySQL Query Error: $error";
    }

    /**
     * @param string $sql       SQL query
     * @param int    $fetchType Fetch type
     * @param array  $extParams Extended params
     * @return array
     */
    public final function query($sql, $fetchType, $extParams)
    {
        if ($extParams['set_names']) {
            $this->_dbSetNames($extParams['set_names']);
        }

        return $this->_query($sql, $fetchType, $extParams['fetch_fields']);
    }

    /**
     * @return bool|null|resource
     */
    protected abstract function _connect();

    /**
     * Additional database handle manipulations - e.g. select DB
     * @param  stdClass $handle DB Handle
     * @return void
     */
    protected abstract function _afterConnect($handle);

    /**
     * Close DB handle
     * @param  stdClass $handle DB Handle
     * @return void
     */
    protected abstract function _closeHandle($handle);

    /**
     * @param string $sql sql query
     * @return array
     */
    public abstract function localQuery($sql);

    /**
     * @param string $sql         Sql query
     * @param int    $fetchType   Fetch Type
     * @param bool   $fetchFields Fetch fields metadata
     * @return array
     */
    protected abstract function _query($sql, $fetchType, $fetchFields = false);

    /**
     * @return string|int
     */
    public function getLastInsertId()
    {
        return $this->_insertedId;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->_affectedRows;
    }

    /**
     * @param  string $charset Charset
     * @return void
     */
    protected abstract function _dbSetNames($charset);

}


class M1_Pdo extends M1_DatabaseLink
{
    public $noResult = array(
        'delete', 'update', 'move', 'truncate', 'insert', 'set', 'create', 'drop', 'replace', 'start transaction', 'commit'
    );

    /**
     * @return bool|PDO
     */
    protected function _connect()
    {
        try {
            $dsn = 'mysql:dbname=' . $this->_config->dbname . ';host=' . $this->_config->host;
            if ($this->_config->port) {
                $dsn .= ';port='. $this->_config->port;
            }
            if ($this->_config->sock != null) {
                $dsn .= ';unix_socket=' . $this->_config->sock;
            }

            $link = new PDO($dsn, $this->_config->username, $this->_config->password);
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $link;

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    protected function _afterConnect($handle)
    {
    }

    /**
     * @inheritdoc
     */
    public function localQuery($sql)
    {
        $result = array();
        /**
         * @var PDO $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();
        $sth = $databaseHandle->query($sql);

        foreach ($this->noResult as $statement) {
            if (!$sth || strpos(strtolower(trim($sql)), $statement) === 0) {
                return true;
            }
        }

        while (($row = $sth->fetch(PDO::FETCH_ASSOC)) != false) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _query($sql, $fetchType, $fetchFields = false)
    {
        $result = array(
            'result'        => null,
            'message'       => '',
            'fetchedFields' => array()
        );

        /**
         * @var PDO $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();

        switch ($fetchType) {
            case 3:
                $databaseHandle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
                break;
            case 2:
                $databaseHandle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
                break;
            case 1:
            default:
                $databaseHandle->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                break;
        }

        try {
            $res = $databaseHandle->query($sql);
            $this->_affectedRows = $res->rowCount();
            $this->_insertedId = $databaseHandle->lastInsertId();
        } catch (PDOException $e) {
            $result['message'] = $this->_errorMsg($e->getCode() . ', ' . $e->getMessage());
            return $result;
        }

        foreach ($this->noResult as $statement) {
            if (!$res || strpos(strtolower(trim($sql)), $statement) === 0) {
                $result['result'] = true;
                return $result;
            }
        }

        $rows = array();
        while (($row = $res->fetch()) !== false) {
            $rows[] = $row;
        }

        if ($fetchFields) {
            $fetchedFields = array();
            $columnCount = $res->columnCount();
            for ($column = 0; $column < $columnCount; $column++) {
                $fetchedFields[] = $res->getColumnMeta($column);
            }
            $result['fetchedFields'] = $fetchedFields;
        }

        $result['result'] = $rows;

        unset($res);
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _closeHandle($handle)
    {
    }

    /**
     * @inheritdoc
     */
    protected function _dbSetNames($charset)
    {
        /**
         * @var PDO $dataBaseHandle
         */
        $dataBaseHandle = $this->_getDatabaseHandle();
        $dataBaseHandle->exec('SET NAMES ' . $dataBaseHandle->quote($charset));
        $dataBaseHandle->exec('SET CHARACTER SET ' . $dataBaseHandle->quote($charset));
        $dataBaseHandle->exec('SET CHARACTER_SET_CONNECTION = ' . $dataBaseHandle->quote($charset));
    }

}

class M1_Mysqli extends M1_DatabaseLink
{
    protected function _connect()
    {
        return @mysqli_connect(
            $this->_config->host,
            $this->_config->username,
            $this->_config->password,
            $this->_config->dbname,
            $this->_config->port ? $this->_config->port : null,
            $this->_config->sock
        );
    }

    /**
     * @param  mysqli $handle DB Handle
     * @return void
     */
    protected function _afterConnect($handle)
    {
        mysqli_select_db($handle, $this->_config->dbname);
    }

    /**
     * @inheritdoc
     */
    public function localQuery($sql)
    {
        $result = array();
        /**
         * @var mysqli $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();
        $sth = mysqli_query($databaseHandle, $sql);
        if (is_bool($sth)) {
            return $sth;
        }
        while (($row = mysqli_fetch_assoc($sth))) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _query($sql, $fetchType, $fetchFields = false)
    {
        $result = array(
            'result'        => null,
            'message'       => '',
            'fetchedFields' => ''
        );

        $fetchMode = MYSQLI_ASSOC;
        switch ($fetchType) {
            case 3:
                $fetchMode = MYSQLI_BOTH;
                break;
            case 2:
                $fetchMode = MYSQLI_NUM;
                break;
            case 1:
                $fetchMode = MYSQLI_ASSOC;
                break;
            default:
                break;
        }

        /**
         * @var mysqli $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();

        $res = mysqli_query($databaseHandle, $sql);

        $triesCount = 10;
        while (mysqli_errno($databaseHandle) == 2013) {
            if (!$triesCount--) {
                break;
            }
            // reconnect
            $this->_releaseHandle();
            if (isset($_REQUEST['set_names'])) {
                mysqli_set_charset($databaseHandle, $_REQUEST['set_names']);
            }

            // execute query once again
            $res = mysqli_query($databaseHandle, $sql);
        }

        if (($errno = mysqli_errno($databaseHandle)) != 0) {
            $result['message'] = $this->_errorMsg($errno . ', ' . mysqli_error($databaseHandle));
            return $result;
        }

        $this->_affectedRows = mysqli_affected_rows($databaseHandle);
        $this->_insertedId = mysqli_insert_id($databaseHandle);

        if (is_bool($res)) {
            $result['result'] = $res;
            return $result;
        }

        if ($fetchFields) {
            $result['fetchedFields'] = mysqli_fetch_fields($res);
        }


        $rows = array();
        while ($row = mysqli_fetch_array($res, $fetchMode)) {
            $rows[] = $row;
        }

        $result['result'] = $rows;

        mysqli_free_result($res);

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _dbSetNames($charset)
    {
        /**
         * @var mysqli $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();
        mysqli_set_charset($databaseHandle, $charset);
    }

    /**
     * @param  mysqli $handle DB Handle
     * @return void
     */
    protected function _closeHandle($handle)
    {
        mysqli_close($handle);
    }

}


class M1_Mysql extends M1_DatabaseLink
{

    /**
     * @inheritdoc
     */
    protected function _connect()
    {
        if ($this->_config->sock !== null) {
            $host = $this->_config->host . ':' . $this->_config->sock;
        } else {
            $host = $this->_config->host . ($this->_config->port ? ':' . $this->_config->port : '');
        }
        $password = stripslashes($this->_config->password);
        $link = @mysql_connect($host, $this->_config->username, $password);
        return $link;
    }

    /**
     * @inheritdoc
     */
    protected function _afterConnect($handle)
    {
        mysql_select_db($this->_config->dbname, $handle);
    }

    /**
     * @inheritdoc
     */
    public function localQuery($sql)
    {
        $result = array();
        $sth = mysql_query($sql, $this->_getDatabaseHandle());
        if (is_bool($sth)) {
            return $sth;
        }
        while (($row = mysql_fetch_assoc($sth)) != false) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _query($sql, $fetchType, $fetchFields = false)
    {
        $result = array(
            'result'  => null,
            'message' => '',
        );

        $fetchMode = MYSQL_ASSOC;
        switch ($fetchType) {
            case 3:
                $fetchMode = MYSQL_BOTH;
                break;
            case 2:
                $fetchMode = MYSQL_NUM;
                break;
            case 1:
                $fetchMode = MYSQL_ASSOC;
                break;
            default:
                break;
        }

        /**
         * @var resource $databaseHandle
         */
        $databaseHandle = $this->_getDatabaseHandle();

        $res = mysql_query($sql, $databaseHandle);

        $triesCount = 10;
        while (mysql_errno($databaseHandle) == 2013) {
            if (!$triesCount--) {
                break;
            }
            // reconnect
            $this->_releaseHandle();

            if (isset($_REQUEST['set_names'])) {
                mysql_set_charset($_REQUEST['set_names'], $databaseHandle);
            }
            // execute query once again
            $res = mysql_query($sql, $databaseHandle);
        }

        if (($errno = mysql_errno($databaseHandle)) != 0) {
            $result['message'] = '[ERROR] Mysql Query Error: ' . $errno . ', ' . mysql_error($databaseHandle);
            return $result;
        }

        $this->_affectedRows = mysql_affected_rows($databaseHandle);
        $this->_insertedId = mysql_insert_id($databaseHandle);

        if (!is_resource($res)) {
            $result['result'] = $res;
            return $result;
        }

        if ($fetchFields) {
            $fetchedFields = array();
            while (($field = mysql_fetch_field($res)) !== false) {
                $fetchedFields[] = $field;
            }
            $result['fetchedFields'] = $fetchedFields;
        }

        $rows = array();
        while (($row = mysql_fetch_array($res, $fetchMode)) !== false) {
            $rows[] = $row;
        }

        $result['result'] = $rows;
        mysql_free_result($res);
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _closeHandle($handle)
    {
        mysql_close($handle);
    }

    /**
     * @inheritdoc
     */
    protected function _dbSetNames($charset)
    {
        mysql_set_charset($charset, $this->_getDatabaseHandle());
    }

}


class M1_Config_Adapter implements M1_Platform_Actions
{
    public $host                = 'localhost';
    public $port                = null;//"3306";
    public $sock                = null;
    public $username            = 'root';
    public $password            = '';
    public $dbname              = '';
    public $tblPrefix           = '';
    public $timeZone            = null;

    public $cartType                 = 'Oscommerce22ms2';
    public $cartId                   = '';
    public $imagesDir                = '';
    public $categoriesImagesDir      = '';
    public $productsImagesDir        = '';
    public $manufacturersImagesDir   = '';
    public $categoriesImagesDirs     = '';
    public $productsImagesDirs       = '';
    public $manufacturersImagesDirs  = '';

    public $languages   = array();
    public $cartVars    = array();

    /**
     * @return mixed
     */
    public function create()
    {
        $cartType = $this->_detectCartType();
        $className = "M1_Config_Adapter_" . $cartType;

        $obj = new $className();
        $obj->cartType = $cartType;

        return $obj;
    }

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function productUpdateAction(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function sendEmailNotifications(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @param array $a2cData Data
     *
     * @return mixed
     */
    public function triggerEvents(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @return mixed
     */
    public function getPlugins()
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function setMetaData(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function getTranslations(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function setOrderNotes(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function getActiveModules(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function getImagesUrls(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * @inheritDoc
     */
    public function orderUpdate(array $a2cData)
    {
        return array('error' => 'Action is not supported', 'data' => false);
    }

    /**
     * Get Card ID string from request parameters
     * @return string
     */
    protected function _getRequestCartId()
    {
        return isset($_POST['cart_id']) ? $_POST['cart_id'] : '';
    }

    /**
     * @return string
     */
    private function _detectCartType()
    {
        switch ($this->_getRequestCartId()) {
            default :
            case 'Prestashop':
                if (file_exists(M1_STORE_BASE_DIR . "config/config.inc.php")) {
                    return "Prestashop";
                }
            case 'Ubercart':
                if (file_exists(M1_STORE_BASE_DIR . 'sites/default/settings.php')) {
                    if (file_exists(M1_STORE_BASE_DIR . '/modules/ubercart/uc_store/includes/coder_review_uc3x.inc') ||
                        file_exists(M1_STORE_BASE_DIR . '/sites/all/modules/ubercart/uc_store/includes/coder_review_uc3x.inc')) {
                        return "Ubercart3";
                    } elseif (file_exists(
                        M1_STORE_BASE_DIR
                        . 'sites/all/modules/commerce/includes/commerce.controller.inc'
                    )) {
                        return "DrupalCommerce";
                    }
                    return "Ubercart";
                }
            case 'Woocommerce':
            case 'WPecommerce':
                if (file_exists(M1_STORE_BASE_DIR . 'wp-config.php') || file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'wp-config.php')) {
                    return 'Wordpress';
                }
            case 'Zencart137':
                if (file_exists(
                        M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR
                        . "configure.php"
                    )
                    && file_exists(M1_STORE_BASE_DIR . "ipn_main_handler.php")
                ) {
                    return "Zencart137";
                }
            case 'Oscommerce22ms2':
                if (file_exists(
                        M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR
                        . "configure.php"
                    )
                    && !file_exists(
                        M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR
                        . "toc_constants.php"
                    )
                ) {
                    return "Oscommerce22ms2";
                }
            case 'Gambio':
                if (file_exists(M1_STORE_BASE_DIR . "/includes/configure.php")) {
                    return "Gambio";
                }
            case 'JooCart':
                if (file_exists(
                    M1_STORE_BASE_DIR . '/components/com_opencart/opencart.php'
                )) {
                    return 'JooCart';
                }
            case 'Mijoshop':
                if (file_exists(
                    M1_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop.php'
                )) {
                    return 'Mijoshop';
                }
            case 'AceShop':
                if (file_exists(
                    M1_STORE_BASE_DIR . '/components/com_aceshop/aceshop.php'
                )) {
                    return 'AceShop';
                }
            case 'Oxid':
                if (file_exists(M1_STORE_BASE_DIR . 'config.inc.php')) {
                    return 'Oxid';
                }
            case 'Virtuemart113':
                if (file_exists(M1_STORE_BASE_DIR . "configuration.php")) {
                    return "Virtuemart113";
                }
            case 'Pinnacle361':
                if (file_exists(
                    M1_STORE_BASE_DIR . 'content/engine/engine_config.php'
                )) {
                    return "Pinnacle361";
                }
            case 'Zoey':
                if (file_exists(M1_STORE_BASE_DIR . 'lib/Zoey/Redis/Overlord.php')) {
                    return 'Zoey';
                }
            case 'Magento1212':
                if (file_exists(M1_STORE_BASE_DIR . 'app/etc/local.xml')
                    || file_exists(M1_STORE_BASE_DIR . 'app/etc/env.php')
                    || file_exists(M1_STORE_BASE_DIR . '/../app/etc/env.php')//if pub is a document root
                ) {
                    return "Magento1212";
                }
            case 'Cubecart':
                if (file_exists(M1_STORE_BASE_DIR . 'includes/global.inc.php')) {
                    return "Cubecart";
                }
            case 'Cscart203':
                if (file_exists(M1_STORE_BASE_DIR . "config.local.php")
                    || file_exists(M1_STORE_BASE_DIR . "prepare.php") && file_exists(M1_STORE_BASE_DIR . "init.php")
                ) {
                    return "Cscart203";
                }
            case 'Opencart14':
                if ((file_exists(M1_STORE_BASE_DIR . "system/startup.php")
                        || (file_exists(M1_STORE_BASE_DIR . "common.php"))
                        || (file_exists(M1_STORE_BASE_DIR . "library/locator.php"))
                    )
                    && file_exists(M1_STORE_BASE_DIR . "config.php")
                ) {
                    return "Opencart14";
                }
            case 'Shopware':
                if (file_exists(M1_STORE_BASE_DIR . "config.php") && file_exists(M1_STORE_BASE_DIR . "shopware.php")
                    || file_exists(M1_STORE_BASE_DIR . '.env')
                    || file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env')
                ) {
                    return "Shopware";
                }
            case 'LemonStand':
                if (file_exists(M1_STORE_BASE_DIR . "boot.php")) {
                    return "LemonStand";
                }
            case 'Interspire':
                if (file_exists(M1_STORE_BASE_DIR . "config/config.php")) {
                    return "Interspire";
                }
            case 'Squirrelcart242':
                if (file_exists(M1_STORE_BASE_DIR . 'squirrelcart/config.php')) {
                    return "Squirrelcart242";
                }
            case 'WebAsyst':
                if (file_exists(M1_STORE_BASE_DIR . 'kernel/wbs.xml')) {
                    return "WebAsyst";
                }
            case 'SSFree':
                if (file_exists(M1_STORE_BASE_DIR . 'cfg/general.inc.php')
                    && file_exists(
                        M1_STORE_BASE_DIR . 'cfg/connect.inc.php'
                    )
                ) {
                    return "SSFree";
                }
            case 'SSPremium':
                //Shopscript Premium
                if (file_exists(M1_STORE_BASE_DIR . 'cfg/connect.inc.php')) {
                    return "SSPremium";
                }

                //ShopScript5
                if (file_exists(M1_STORE_BASE_DIR . 'wa.php')
                    && file_exists(
                        M1_STORE_BASE_DIR . 'wa-config/db.php'
                    )
                ) {
                    return "SSPremium";
                }
            case 'Summercart3':
                if (file_exists(M1_STORE_BASE_DIR . 'sclic.lic')
                    && file_exists(
                        M1_STORE_BASE_DIR . 'include/miphpf/Config.php'
                    )
                ) {
                    return "Summercart3";
                }
            case 'XtcommerceVeyton':
            case 'Xtcommerce':
                if (file_exists(M1_STORE_BASE_DIR . 'conf/config.php')) {
                    return "XtcommerceVeyton";
                }
            case 'XCart':
                if (file_exists(M1_STORE_BASE_DIR . 'config.php')
                    || (file_exists(M1_STORE_BASE_DIR . '/etc/config.php'))
                ) {
                    return "XCart";
                }
            case 'Hhgmultistore':
                if (file_exists(M1_STORE_BASE_DIR . 'core/config/configure.php')) {
                    return 'Hhgmultistore';
                }
            case 'Sunshop4':
                if (file_exists(
                        M1_STORE_BASE_DIR . "include" . DIRECTORY_SEPARATOR . "config.php"
                    )
                    || file_exists(
                        M1_STORE_BASE_DIR . "include" . DIRECTORY_SEPARATOR
                        . "db_mysql.php"
                    )
                ) {
                    return "Sunshop4";
                }
            case 'Tomatocart':
                if (file_exists(
                        M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR
                        . "configure.php"
                    )
                    && file_exists(
                        M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR
                        . "toc_constants.php"
                    )
                ) {
                    return 'Tomatocart';
                }
        }
        die ("BRIDGE_ERROR_CONFIGURATION_NOT_FOUND");
    }

    /**
     * @param $cartType
     * @return string
     */
    public function getAdapterPath($cartType)
    {
        return M1_STORE_BASE_DIR . M1_BRIDGE_DIRECTORY_NAME . DIRECTORY_SEPARATOR
            . "app" . DIRECTORY_SEPARATOR
            . "class" . DIRECTORY_SEPARATOR
            . "config_adapter" . DIRECTORY_SEPARATOR . $cartType . ".php";
    }

    /**
     * @param $source
     */
    public function setHostPort($source)
    {
        $source = trim($source);

        if ($source == '') {
            $this->host = 'localhost';
            return;
        }

        if (strpos($source, '.sock') !== false) {
            $socket = ltrim($source, 'localhost:');
            $socket = ltrim($socket, '127.0.0.1:');

            $this->host = 'localhost';
            $this->sock = $socket;

            return;
        }

        $conf = explode(":", $source);

        if (isset($conf[0]) && isset($conf[1])) {
            $this->host = $conf[0];
            $this->port = $conf[1];
        } elseif ($source[0] == '/') {
            $this->host = 'localhost';
            $this->port = $source;
        } else {
            $this->host = $source;
        }
    }

    /**
     * @return bool|M1_Mysql|M1_Mysqli|M1_Pdo
     */
    public function connect()
    {
        if (extension_loaded('pdo_mysql')) {
            $link = new M1_Pdo($this);
        } elseif (function_exists('mysqli_connect')) {
            $link = new M1_Mysqli($this);
        } elseif (function_exists('mysql_connect')) {
            $link = new M1_Mysql($this);
        } else {
            $link = false;
        }

        return $link;
    }

    /**
     * @param $field
     * @param $tableName
     * @param $where
     * @return string
     */
    public function getCartVersionFromDb($field, $tableName, $where)
    {
        $version = '';

        $link = $this->connect();
        if (!$link) {
            return '[ERROR] MySQL Query Error: Can not connect to DB';
        }

        $result = $link->localQuery("
      SELECT " . $field . " AS version
      FROM " . $this->tblPrefix . $tableName . "
      WHERE " . $where
        );

        if (is_array($result) && isset($result[0]['version'])) {
            $version = $result[0]['version'];
        }

        return $version;
    }
}

class M1_Bridge
{
    /**
     * @var M1_DatabaseLink|null
     */
    protected $_link  = null; //mysql connection link
    public $config    = null; //config adapter

    /**
     * Bridge constructor
     *
     * M1_Bridge constructor.
     * @param $config
     */
    public function __construct(M1_Config_Adapter $config)
    {
        $this->config = $config;

        if ($this->getAction() != "savefile") {
            $this->_link = $this->config->connect();
        }
    }

    /**
     * @return mixed
     */
    public function getTablesPrefix()
    {
        return $this->config->tblPrefix;
    }

    /**
     * @return M1_DatabaseLink|null
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * @return mixed|string
     */
    private function getAction()
    {
        if (isset($_POST['action'])) {
            return str_replace('.', '', $_POST['action']);
        }

        return '';
    }

    public function run()
    {
        $action = $this->getAction();

        if ($action == "checkbridge") {
            echo "BRIDGE_OK";
            return;
        }

        $this->validateSign();

        if ($action == "update") {
            $this->_checkPossibilityUpdate();
        }

        $className = "M1_Bridge_Action_" . ucfirst($action);
        if (!class_exists($className)) {
            echo 'ACTION_DO_NOT EXIST' . PHP_EOL;
            die;
        }

        $actionObj = new $className();
        @$actionObj->cartType = @$this->config->cartType;
        $actionObj->perform($this);
        $this->_destroy();
    }

    private function validateSign()
    {
        if (isset($_GET['token'])) {
            exit('ERROR: Field token is not correct');
        }

        if (empty($_POST)) {
            exit('BRIDGE INSTALLED.<br /> Version: ' . M1_BRIDGE_VERSION);
        }

        if (isset($_POST['a2c_sign'])) {
            $sign = $_POST['a2c_sign'];
        } else {
            exit('ERROR: Signature is not correct');
        }

        unset($_POST['a2c_sign']);
        ksort($_POST, SORT_STRING);
        $resSign = hash_hmac('sha256', http_build_query($_POST), M1_TOKEN);

        if ($sign !== $resSign) {
            exit('ERROR: Signature is not correct');
        }

        show_error(1);
    }

    /**
     * @param $dir
     * @return bool
     */
    private function isWritable($dir)
    {
        if (!@is_dir($dir)) {
            return false;
        }

        $dh = @opendir($dir);

        if ($dh === false) {
            return false;
        }

        while (($entry = readdir($dh)) !== false) {
            if ($entry == "." || $entry == ".." || !@is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }

            if (!$this->isWritable($dir . DIRECTORY_SEPARATOR . $entry)) {
                return false;
            }
        }

        if (!is_writable($dir)) {
            return false;
        }

        return true;
    }

    private function _destroy()
    {
        $this->_link = null;
    }

    private function _checkPossibilityUpdate()
    {
        if (!is_writable(__DIR__)) {
            die("ERROR_BRIDGE_DIR_IS_NOT_WRITABLE");
        }

        if (!is_writable(__FILE__)) {
            die("ERROR_BRIDGE_IS_NOT_WRITABLE");
        }
    }

    private function _selfTest()
    {
        if (isset($_GET['token'])) {
            if ($_GET['token'] === M1_TOKEN) {
                // good :)
            } else {
                die('ERROR_INVALID_TOKEN');
            }
        } else{
            die('BRIDGE INSTALLED.<br /> Version: ' . M1_BRIDGE_VERSION);
        }
    }

    /**
     * Remove php comments from string
     * @param string $str
     */
    public static function removeComments($str)
    {
        $result  = '';
        $commentTokens = array(T_COMMENT, T_DOC_COMMENT);
        $tokens = token_get_all($str);

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens))
                    continue;
                $token = $token[1];
            }
            $result .= $token;
        }

        return $result;
    }

    /**
     * @param $str
     * @param string $constNames
     * @param bool $onlyString
     * @return array
     */
    public static function parseDefinedConstants($str, $constNames = '\w+', $onlyString = true )
    {
        $res = array();
        $pattern = '/define\s*\(\s*[\'"](' . $constNames . ')[\'"]\s*,\s*'
            . ($onlyString ? '[\'"]' : '') . '(.*?)' . ($onlyString ? '[\'"]' : '') . '\s*\)\s*;/';

        preg_match_all($pattern, $str, $matches);

        if (isset($matches[1]) && isset($matches[2])) {
            foreach ($matches[1] as $key => $constName) {
                $res[$constName] = $matches[2][$key];
            }
        }

        return $res;
    }

}


/**
 * Class M1_Config_Adapter_Zoey
 */
class M1_Config_Adapter_Zoey extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Zoey constructor.
     */
    public function __construct()
    {
        /**
         * @var SimpleXMLElement
         */
        $config = simplexml_load_file(M1_STORE_BASE_DIR . 'app/etc/local.xml');

        $this->cartVars['dbPrefix'] = (string)$config->global->resources->db->table_prefix;
        $this->tblPrefix = (string)$config->global->resources->db->table_prefix;

        include_once M1_STORE_BASE_DIR . 'app/Mage.php';
        $this->cartVars['dbVersion'] = Mage::getVersion();

        if (Zoey_Redis_Overlord::isEnabled()) {
            $storeRedis = Zoey_Redis_Overlord::getStoreCache();
            $config = $storeRedis->setDatabaseConnectionVars($config);

            $this->setHostPort((string)$config->host);
            $this->password  = (string)$config->password;
            $this->username  = (string)$config->username;
            $this->dbname    = (string)$config->dbname;
        } else {
            $this->setHostPort((string)$config->global->resources->default_setup->connection->host);
            $this->username  = (string)$config->global->resources->default_setup->connection->username;
            $this->dbname    = (string)$config->global->resources->default_setup->connection->dbname;
            $this->password  = (string)$config->global->resources->default_setup->connection->password;
        }

        $this->imagesDir              = 'media/';
        $this->categoriesImagesDir    = $this->imagesDir . 'catalog/category/';
        $this->productsImagesDir      = $this->imagesDir . 'catalog/product/';
        $this->manufacturersImagesDir = $this->imagesDir;
    }
}

/**
 * Class M1_Config_Adapter_Zencart137
 */
class M1_Config_Adapter_Zencart137 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Zencart137 constructor.
     */
    public function __construct()
    {
        $curDir = getcwd();

        chdir(M1_STORE_BASE_DIR);

        @require_once M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "configure.php";
        if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'defined_paths.php')) {
            @require_once M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "defined_paths.php";
        }

        chdir($curDir);

        $this->imagesDir = DIR_WS_IMAGES;

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        if (defined('DIR_WS_PRODUCT_IMAGES')) {
            $this->productsImagesDir = DIR_WS_PRODUCT_IMAGES;
        }
        if (defined('DIR_WS_ORIGINAL_IMAGES')) {
            $this->productsImagesDir = DIR_WS_ORIGINAL_IMAGES;
        }
        $this->manufacturersImagesDir = $this->imagesDir;

        $this->setHostPort(DB_SERVER);
        $this->username  = DB_SERVER_USERNAME;
        $this->password  = DB_SERVER_PASSWORD;
        $this->dbname    = DB_DATABASE;

        if (defined('DB_PREFIX')) {
            $this->tblPrefix = DB_PREFIX;
        } else {
            $this->tblPrefix = '';
        }

        if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'version.php')) {
            @require_once M1_STORE_BASE_DIR
                . "includes" . DIRECTORY_SEPARATOR
                . "version.php";
            $major = PROJECT_VERSION_MAJOR;
            $minor = PROJECT_VERSION_MINOR;
            if (defined('EXPECTED_DATABASE_VERSION_MAJOR') && EXPECTED_DATABASE_VERSION_MAJOR != '' ) {
                $major = EXPECTED_DATABASE_VERSION_MAJOR;
            }
            if (defined('EXPECTED_DATABASE_VERSION_MINOR') && EXPECTED_DATABASE_VERSION_MINOR != '' ) {
                $minor = EXPECTED_DATABASE_VERSION_MINOR;
            }

            if ($major != '' && $minor != '') {
                $this->cartVars['dbVersion'] = $major.'.'.$minor;
            }

        }
    }

}



/**
 * Class M1_Config_Adapter_XtcommerceVeyton
 */
class M1_Config_Adapter_XtcommerceVeyton extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_XtcommerceVeyton constructor.
     */
    public function __construct()
    {
        define('_VALID_CALL','TRUE');
        define('_SRV_WEBROOT','TRUE');
        require_once M1_STORE_BASE_DIR
            . 'conf'
            . DIRECTORY_SEPARATOR
            . 'config.php';

        require_once M1_STORE_BASE_DIR
            . 'conf'
            . DIRECTORY_SEPARATOR
            . 'paths.php';

        $this->setHostPort(_SYSTEM_DATABASE_HOST);
        $this->dbname = _SYSTEM_DATABASE_DATABASE;
        $this->username = _SYSTEM_DATABASE_USER;
        $this->password = _SYSTEM_DATABASE_PWD;
        $this->imagesDir = _SRV_WEB_IMAGES;
        $this->tblPrefix = DB_PREFIX . "_";

        try {
            $timeZone = date_default_timezone_get();
        } catch (Exception $e) {
            $timeZone = 'UTC';
        }

        $this->timeZone = $timeZone;
        $version = $this->getCartVersionFromDb("config_value", "config", "config_key = '_SYSTEM_VERSION'");
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        } elseif (M1_STORE_BASE_DIR . 'versioninfo.php') {
            $file = file_get_contents(M1_STORE_BASE_DIR . 'versioninfo.php');

            if (strpos($file, 'File is part of xt:Commerce') !== false) {
                $start = strpos($file, 'File is part of xt:Commerce');
                $version = trim(substr($file, $start + 27, 8));
            } else {
                $version = '';
            }

            $this->cartVars['dbVersion'] = $version;
        }

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }
}


/**
 * Class M1_Config_Adapter_XCart
 */
class M1_Config_Adapter_XCart extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_XCart constructor.
     */
    public function __construct()
    {
        define('XCART_START', 1);

        if (file_exists(M1_STORE_BASE_DIR . "config.php")) {
            $this->_xcart();
        } else {
            $this->_xcart5();
        }
    }

    /**
     * @return void
     */
    protected function _xcart()
    {
        $config = file_get_contents(M1_STORE_BASE_DIR . "config.php");

        try {
            preg_match('/\$sql_host.+\'(.+)\';/', $config, $match);
            $this->setHostPort($match[1]);
            preg_match('/\$sql_user.+\'(.+)\';/', $config, $match);
            $this->username = $match[1];
            preg_match('/\$sql_db.+\'(.+)\';/', $config, $match);
            $this->dbname = $match[1];
            preg_match('/\$sql_password.+\'(.*)\';/', $config, $match);
            $this->password = $match[1];
        } catch (Exception $e) {
            die('ERROR_READING_STORE_CONFIG_FILE');
        }

        $this->imagesDir = 'images/'; // xcart starting from 4.1.x hardcodes images location
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (file_exists(M1_STORE_BASE_DIR . "VERSION")) {
            $version = file_get_contents(M1_STORE_BASE_DIR . "VERSION");
            $this->cartVars['dbVersion'] = preg_replace('/(Version| |\\n)/', '', $version);
        }
    }

    /**
     * @return void
     */
    protected function _xcart5()
    {
        $config = M1_STORE_BASE_DIR .'/etc/config.php';
        $this->imagesDir = "/images";
        $this->categoriesImagesDir    = $this->imagesDir."/category";
        $this->productsImagesDir      = $this->imagesDir."/product";
        $this->manufacturersImagesDir = $this->imagesDir;

        $settings = parse_ini_file($config, true);
        $settings = $settings['database_details'];
        $this->host      = $settings['hostspec'];
        $this->setHostPort($settings['hostspec']);
        $this->username  = $settings['username'];
        $this->password  = $settings['password'];
        $this->dbname    = $settings['database'];
        $this->tblPrefix = $settings['table_prefix'];

        $version = $this->getCartVersionFromDb("value", "config", "name = 'version'");
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }
    }
}

/**
 * Class M1_Config_Adapter_Wordpress
 */
class M1_Config_Adapter_Wordpress extends M1_Config_Adapter
{

    const ERROR_CODE_SUCCESS = 0;
    const ERROR_CODE_ENTITY_NOT_FOUND = 1;
    const ERROR_CODE_INTERNAL_ERROR = 2;

    private $_multiSiteEnabled = false;
    private $_pluginName = '';

    /**
     * M1_Config_Adapter_Wordpress constructor.
     */
    public function __construct()
    {
        if (file_exists(M1_STORE_BASE_DIR . 'wp-config.php')) {
            $config = file_get_contents(M1_STORE_BASE_DIR . 'wp-config.php');
        } elseif (file_exists(realpath(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'wp-config.php'))) {
            $config = file_get_contents(realpath(M1_STORE_BASE_DIR . '../wp-config.php'));
        }

        if (isset($config)) {
            $configs =  M1_Bridge::removeComments($config);
            $constants = M1_Bridge::parseDefinedConstants($configs, 'DB_NAME|DB_USER|DB_PASSWORD|DB_HOST|UPLOADS|WP_HOME|WP_SITEURL|WP_CONTENT_URL');
            preg_match('/\$table_prefix\s*=\s*[\'"](.+?)[\'"]\s*;/', $configs, $tblPrefixMatch);

            if (!isset($constants['DB_NAME'], $constants['DB_USER'], $constants['DB_PASSWORD'], $constants['DB_HOST'], $tblPrefixMatch[1]) || $this->hasUrlProgrammed($constants)) {
                $this->_tryLoadConfigs();
            } else {
                $multiSiteSettings = M1_Bridge::parseDefinedConstants($configs, 'MULTISITE', false);

                $this->_multiSiteEnabled = isset($multiSiteSettings['MULTISITE']) && $multiSiteSettings['MULTISITE'] === 'true';
                $this->dbname   = $constants['DB_NAME'];
                $this->username = $constants['DB_USER'];
                $this->password = $constants['DB_PASSWORD'];
                $this->setHostPort($constants['DB_HOST']);
                $this->tblPrefix = $tblPrefixMatch[1];

                if (isset($constants['UPLOADS'])) {
                    $this->imagesDir = preg_replace('/\'\.\'/', '', $constants['UPLOADS']);
                } else {
                    $this->imagesDir = 'wp-content' . DIRECTORY_SEPARATOR . 'uploads';
                }

                if (!$this->_multiSiteEnabled) {
                    if (isset($constants['WP_HOME'])) {
                        $this->cartVars['wp_home'] = $constants['WP_HOME'];
                    }

                    if (isset($constants['WP_SITEURL'])) {
                        $this->cartVars['wp_siteurl'] = $constants['WP_SITEURL'];
                    }

                    if (isset($constants['WP_CONTENT_URL'])) {
                        $this->cartVars['wp_content_url'] =  $constants['WP_CONTENT_URL'];
                    }
                } elseif (isset($constants['WP_CONTENT_URL'])) {
                    $this->cartVars['wp_content_url'] =  $constants['WP_CONTENT_URL'];
                }
            }
        } else {
            $this->_tryLoadConfigs();
        }

        $getActivePlugin = function(array $cartPlugins) {
            foreach ($cartPlugins as $plugin) {
                if ($cartId = $this->_getRequestCartId()) {
                    if ($cartId == 'Woocommerce' && strpos($plugin, 'woocommerce.php') !== false) {
                        return 'woocommerce';
                    } elseif ($cartId == 'WPecommerce' && (strpos($plugin, 'wp-e-commerce') === 0 || strpos($plugin, 'wp-ecommerce') === 0)) {
                        return 'wp-e-commerce';
                    }
                } else {
                    if (strpos($plugin, 'woocommerce.php') !== false) {
                        return 'woocommerce';
                    } elseif (strpos($plugin, 'wp-e-commerce') === 0 || strpos($plugin, 'wp-ecommerce') === 0) {
                        return 'wp-e-commerce';
                    }
                }
            };

            return false;
        };

        $activePlugin = false;
        $wpTblPrefix = $this->tblPrefix;

        if ($this->_multiSiteEnabled) {
            $cartPluginsNetwork = $this->getCartVersionFromDb(
                "meta_value", "sitemeta", "meta_key = 'active_sitewide_plugins'"
            );

            if ($cartPluginsNetwork) {
                $cartPluginsNetwork = unserialize($cartPluginsNetwork);
                $activePlugin = $getActivePlugin(array_keys($cartPluginsNetwork));
            }

            if ($activePlugin === false) {
                if ($link = $this->connect()) {
                    $blogs = $link->localQuery('SELECT blog_id FROM ' . $this->tblPrefix . 'blogs');
                    if ($blogs) {
                        foreach ($blogs as $blog) {
                            if ($blog['blog_id'] > 1) {
                                $this->tblPrefix = $this->tblPrefix . $blog['blog_id'] . '_';
                            }

                            $cartPlugins = $this->getCartVersionFromDb("option_value", "options", "option_name = 'active_plugins'");
                            if ($cartPlugins) {
                                $activePlugin = $getActivePlugin(unserialize($cartPlugins));
                            }

                            if ($activePlugin) {
                                break;
                            } else {
                                $this->tblPrefix = $wpTblPrefix;
                            }
                        }
                    }
                } else {
                    return '[ERROR] MySQL Query Error: Can not connect to DB';
                }
            }
        } else {
            $cartPlugins = $this->getCartVersionFromDb("option_value", "options", "option_name = 'active_plugins'");
            if ($cartPlugins) {
                $activePlugin = $getActivePlugin(unserialize($cartPlugins));
            }
        }

        if ($activePlugin == 'woocommerce') {
            $this->_setWoocommerceData();
        } elseif($activePlugin == 'wp-e-commerce') {
            $this->_setWpecommerceData();
        } else {
            die ("CART_PLUGIN_IS_NOT_DETECTED");
        }

        $this->_pluginName = $activePlugin;
        $this->tblPrefix = $wpTblPrefix;
    }

    protected function _setWoocommerceData()
    {
        $this->cartId = "Woocommerce";
        $version = $this->getCartVersionFromDb("option_value", "options", "option_name = 'woocommerce_db_version'");

        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }

        $this->cartVars['categoriesDirRelative'] = 'images/categories/';
        $this->cartVars['productsDirRelative'] = 'images/products/';
    }

    /**
     * @param array $constants Constants
     *
     * @return bool
     */
    protected function hasUrlProgrammed($constants)
    {
        $validHomeUrl = false;
        $validSiteUrl = false;

        if (isset($constants['WP_HOME'])) {
            $validHomeUrl = filter_var($constants['WP_HOME'], FILTER_VALIDATE_URL) !== false;
        } elseif (isset($constants['WP_SITEURL'])) {
            $validSiteUrl = filter_var($constants['WP_SITEURL'], FILTER_VALIDATE_URL) !== false;
        }

        return $validHomeUrl || $validSiteUrl;
    }

    /**
     * @return void
     */
    private function _resetGlobalVars()
    {
        foreach($GLOBALS as $varname => $value)
        {
            global $$varname; //$$ is no mistake here

            $$varname = $value;
        }
    }

    protected function _setWpecommerceData()
    {
        $this->cartId = "Wpecommerce";
        $version = $this->getCartVersionFromDb("option_value", "options", "option_name = 'wpsc_version'");
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        } else {
            $filePath = M1_STORE_BASE_DIR . "wp-content" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR
                . "wp-shopping-cart" . DIRECTORY_SEPARATOR . "wp-shopping-cart.php";
            if (file_exists($filePath)) {
                $conf = file_get_contents ($filePath);
                preg_match("/define\('WPSC_VERSION.*/", $conf, $match);
                if (isset($match[0]) && !empty($match[0])) {
                    preg_match("/\d.*/", $match[0], $project);
                    if (isset($project[0]) && !empty($project[0])) {
                        $version = $project[0];
                        $version = str_replace(array(" ","-","_","'",");",")",";"), "", $version);
                        if ($version != '') {
                            $this->cartVars['dbVersion'] = strtolower($version);
                        }
                    }
                }
            }
        }

        if (file_exists(M1_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'shopp' . DIRECTORY_SEPARATOR . 'Shopp.php')
            || file_exists(M1_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-e-commerce' . DIRECTORY_SEPARATOR . 'editor.php')) {
            $this->imagesDir = 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wpsc' . DIRECTORY_SEPARATOR;
            $this->categoriesImagesDir    = $this->imagesDir.'category_images' . DIRECTORY_SEPARATOR;
            $this->productsImagesDir      = $this->imagesDir.'product_images' . DIRECTORY_SEPARATOR;
            $this->manufacturersImagesDir = $this->imagesDir;
        } elseif (file_exists(M1_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-e-commerce' . DIRECTORY_SEPARATOR . 'wp-shopping-cart.php')) {
            $this->imagesDir = 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '';
            $this->categoriesImagesDir    = $this->imagesDir.'wpsc' . DIRECTORY_SEPARATOR . 'category_images' . DIRECTORY_SEPARATOR;
            $this->productsImagesDir      = $this->imagesDir;
            $this->manufacturersImagesDir = $this->imagesDir;
        } else {
            $this->imagesDir = 'images' . DIRECTORY_SEPARATOR;
            $this->categoriesImagesDir    = $this->imagesDir;
            $this->productsImagesDir      = $this->imagesDir;
            $this->manufacturersImagesDir = $this->imagesDir;
        }
    }

    /**
     * @return bool
     */
    protected function _tryLoadConfigs()
    {
        try {
            $defaultJsonStr = '{"test":"1"}';
            $_POST['not_escaped'] = $defaultJsonStr;

            if (file_exists(M1_STORE_BASE_DIR . 'wp-load.php')) {
                require_once(M1_STORE_BASE_DIR . 'wp-load.php');
            } else {
                require_once(dirname(M1_STORE_BASE_DIR) . DIRECTORY_SEPARATOR . 'wp-load.php');
            }

            if ($defaultJsonStr !== $_POST['not_escaped']) {
                //WordPress escapes all quotation marks in $_POST variables whether or not PHP's magic_quotes_gpc is enabled. See wp_magic_quotes()
                $_COOKIE  = stripslashes_array($_COOKIE);
                $_GET     = stripslashes_array($_GET);
                $_POST    = stripslashes_array($_POST);
                $_REQUEST = stripslashes_array($_REQUEST);
            }

            unset($_POST['not_escaped']);

            if (defined('DB_NAME') && defined('DB_USER') && defined('DB_HOST')) {
                $this->dbname   = DB_NAME;
                $this->username = DB_USER;
                $this->setHostPort(DB_HOST);
            } else {
                return false;
            }

            if (defined('DB_PASSWORD')) {
                $this->password = DB_PASSWORD;
            } elseif (defined('DB_PASS')) {
                $this->password = DB_PASS;
            } else {
                return false;
            }

            if (defined('WP_CONTENT_DIR')) {
                $this->imagesDir = $this->_relPath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads');
            } elseif (defined('UPLOADS')) {
                $this->imagesDir = $this->_relPath(UPLOADS);
            } else {
                $this->imagesDir = 'wp-content' . DIRECTORY_SEPARATOR . 'uploads';
            }

            if ($this->_multiSiteEnabled = (defined('MULTISITE') && MULTISITE === true)) {
                if (defined('WP_SITEURL')) {
                    $this->cartVars['wp_siteurl'] = WP_SITEURL;
                }

                if (defined('WP_HOME')) {
                    $this->cartVars['wp_home'] = WP_HOME;
                }

                if (defined('WP_CONTENT_URL')) {
                    $this->cartVars['wp_content_url'] = WP_CONTENT_URL;
                }
            } elseif (defined('WP_CONTENT_URL')) {
                $this->cartVars['wp_content_url'] = WP_CONTENT_URL;
            }

            if (isset($table_prefix)) {
                $this->tblPrefix = $table_prefix;
            }
        } catch (Exception $e) {
            die('ERROR_READING_STORE_CONFIG_FILE');
        }

        foreach (get_defined_vars() as $key => $val) {
            $GLOBALS[$key] = $val;
        }

        return true;
    }

    /**
     * @param string $path Absolute path
     *
     * @return string Relative path
     */
    private function _relPath($path)
    {
        $absPath = realpath($path);
        $absBase = realpath(M1_STORE_BASE_DIR);

        return str_replace($absBase, '', $absPath);
    }

    /**
     * @param array $a2cData Notifications data
     *
     * @return mixed
     * @throws Exception
     */
    public function sendEmailNotifications(array $a2cData)
    {
        if ($this->_pluginName === 'woocommerce') {
            return $this->_wcEmailNotification($a2cData);
        } else {
            throw new Exception('Action is not supported');
        }
    }

    /**
     * @param array $a2cData Notifications data
     *
     * @return bool
     */
    private function _wcEmailNotification(array $a2cData)
    {
        require_once M1_STORE_BASE_DIR . '/wp-load.php';

        if (function_exists('switch_to_blog')) {
            switch_to_blog($a2cData['store_id']);
        }

        $emails = WC()->mailer()->get_emails();//init mailer

        foreach ($a2cData['notifications'] as $notification) {
            if (isset($notification['wc_class'])) {
                if (isset($emails[$notification['wc_class']])) {
                    call_user_func_array(array($emails[$notification['wc_class']], 'trigger'), $notification['data']);
                } else {
                    return false;
                }
            } else {
                do_action($notification['wc_action'], $notification['data']);
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function triggerEvents(array $a2cData)
    {
        require_once M1_STORE_BASE_DIR . '/wp-load.php';

        if (function_exists('switch_to_blog')) {
            switch_to_blog($a2cData['store_id']);
        }

        foreach ($a2cData['events'] as $event) {
            if ($event['event'] === 'update') {
                switch ($event['entity_type']) {
                    case 'product':
                        $product = WC()->product_factory->get_product($event['entity_id']);
                        if (in_array( 'stock_status', $event['updated_meta'], true)) {
                            do_action('woocommerce_product_set_stock_status', $product->get_id(), $product->get_stock_status(), $product);
                        }
                        if (in_array('stock_quantity', $event['updated_meta'], true)) {
                            do_action('woocommerce_product_set_stock', $product);
                        }

                        do_action('woocommerce_product_object_updated_props', $product, $event['updated_meta']);
                        break;
                    case 'variant':
                        $product = WC()->product_factory->get_product($event['entity_id']);
                        if (in_array('stock_status', $event['updated_meta'], true)) {
                            do_action('woocommerce_variation_set_stock_status', $event['entity_id'], $product->get_stock_status(), $product);
                        }
                        if (in_array('stock_quantity', $event['updated_meta'], true)) {
                            do_action('woocommerce_variation_set_stock', $product);
                        }

                        do_action('woocommerce_product_object_updated_props', $product, $event['updated_meta']);
                        break;
                    case 'order':
                        $entity = WC()->order_factory->get_order($event['entity_id']);
                        do_action( 'woocommerce_order_status_' . $event['status']['to'], $entity->get_id(), $entity);

                        if (isset($event['status']['from'])) {
                            do_action('woocommerce_order_status_' . $event['status']['from'] . '_to_' . $event['status']['to'], $entity->get_id(), $entity);
                            do_action('woocommerce_order_status_changed', $entity->get_id(), $event['status']['from'], $event['status']['to'], $entity);
                        }
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function setMetaData(array $a2cData)
    {
        $response = [
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        ];

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';

            if (function_exists('switch_to_blog')) {
                switch_to_blog($a2cData['store_id']);
            }

            $id = (int)$a2cData['entity_id'];

            switch ($a2cData['entity']) {
                case 'product':
                    $entity = WC()->product_factory->get_product($id);
                    break;
                case 'order':
                    $entity = WC()->order_factory->get_order($id);
                    break;

                case 'customer':
                    $entity = new WC_Customer($id);
                    break;
            }

            if (!$entity) {
                $response['error_code'] = self::ERROR_CODE_ENTITY_NOT_FOUND;
                $response['error'] = 'Entity not found';
            } else {
                if (isset($a2cData['meta'])) {
                    foreach ($a2cData['meta'] as $key => $value) {
                        $entity->add_meta_data($key, $value, true);
                    }
                }

                if (isset($a2cData['unset_meta'])) {
                    foreach ($a2cData['unset_meta'] as $key) {
                        $entity->delete_meta_data($key);
                    }
                }

                if (isset($a2cData['meta']) || isset($a2cData['unset_meta'])) {
                    $entity->save();

                    if (isset($a2cData['meta'])) {
                        global $wpdb;
                        $wpdb->set_blog_id($a2cData['store_id']);
                        $keys = implode( "', '", $wpdb->_escape(array_keys($a2cData['meta'])));

                        switch ($a2cData['entity']) {
                            case 'product':
                            case 'order':
                                $qRes = $wpdb->get_results("
                SELECT pm.meta_id, pm.meta_key, pm.meta_value
                FROM {$wpdb->postmeta} AS pm
                WHERE pm.post_id = {$id}
                  AND pm.meta_key IN ('{$keys}')"
                                );
                                break;

                            case 'customer':
                                $qRes = $wpdb->get_results("
                SELECT um.umeta_id AS 'meta_id', um.meta_key, um.meta_value
                FROM {$wpdb->usermeta} AS um
                WHERE um.user_id = {$id}
                  AND um.meta_key IN ('{$keys}')"
                                );

                                break;
                        }

                        $response['result']['meta'] = $qRes;
                    }

                    if (isset($a2cData['unset_meta'])) {
                        foreach ($a2cData['unset_meta'] as $key) {
                            $response['result']['removed_meta'][$key] = !(bool)$entity->get_meta($key);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getTranslations(array $a2cData)
    {
        $response = [
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        ];

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';

            if (function_exists('switch_to_blog')) {
                switch_to_blog($a2cData['store_id']);
            }

            foreach ($a2cData['strings'] as $key => $stringData) {
                $response['result'][$key] = __($stringData['id'], $stringData['domain']);
            }
        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function setOrderNotes(array $a2cData)
    {
        $response = array(
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        );

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        $getAdminId = function () {
            global $wpdb;

            $wpUserSearch = $wpdb->get_results("SELECT ID FROM {$wpdb->users} ORDER BY ID");
            $adminId = false;

            foreach ($wpUserSearch as $userId) {
                $currentUser = get_userdata($userId->ID);

                if (!empty($currentUser->user_level) && $currentUser->user_level >= 8) {//levels 8, 9 and 10 are admin
                    $adminId = $userId->ID;
                    break;
                }
            }

            return $adminId;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';

            if (function_exists('switch_to_blog')) {
                switch_to_blog($a2cData['store_id']);
            }

            $order = WC()->order_factory->get_order((int)$a2cData['order_id']);

            if (!$order) {
                $response['error_code'] = self::ERROR_CODE_ENTITY_NOT_FOUND;
                $response['error'] = 'Entity not found';
            } else {
                if (empty($a2cData['from'])) {
                    /* translators: %s: new order status */
                    $transition_note = sprintf(__('Order status set to %s.', 'woocommerce'), wc_get_order_status_name($a2cData['to']));

                    if (empty($a2cData['added_by_user'])) {
                        $order->add_order_note($transition_note);
                    } else {
                        if ($adminId = $getAdminId()) {
                            wp_set_current_user($adminId);
                        }

                        $order->add_order_note($transition_note, 0, true);
                    }
                } else {
                    /* translators: 1: old order status 2: new order status */
                    $transition_note = sprintf(
                        __('Order status changed from %1$s to %2$s.', 'woocommerce'),
                        wc_get_order_status_name($a2cData['from']),
                        wc_get_order_status_name($a2cData['to'])
                    );

                    if (empty($a2cData['added_by_user'])) {
                        $order->add_order_note($transition_note);
                    } else {
                        if ($adminId = $getAdminId()) {
                            wp_set_current_user($adminId);
                        }

                        $order->add_order_note($transition_note, 0, true);
                    }
                }
            }
        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

    /**
     * @param array $a2cData
     *
     * @return array
     */
    public function getImagesUrls(array $a2cData)
    {
        $response = array(
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        );

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';

            foreach ($a2cData as $imagesCollection) {
                if (function_exists('switch_to_blog')) {
                    switch_to_blog($imagesCollection['store_id']);
                }

                $images = array();
                foreach ($imagesCollection['ids'] as $id) {
                    $images[$id] = wp_get_attachment_url($id);
                }

                $response['result'][$imagesCollection['store_id']] = array('images' => $images);
            }
        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        $response = array(
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        );

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';
            require_once M1_STORE_BASE_DIR . 'wp-admin/includes/plugin.php';

            if (function_exists('get_plugins')) {
                $response['result']['plugins'] = get_plugins();
            }

        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

    /**
     * @param array $a2cData Data
     *
     * @return array
     */
    public function orderUpdate(array $a2cData)
    {
        $response = array(
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error' => null,
            'result' => array()
        );

        $reportError = function ($e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        try {
            require_once M1_STORE_BASE_DIR . '/wp-load.php';

            foreach (get_defined_vars() as $key => $val) {
                $GLOBALS[$key] = $val;
            }

            $this->_resetGlobalVars();

            if (function_exists('switch_to_blog')) {
                switch_to_blog($a2cData['order']['store_id']);
            }

            $entity = WC()->order_factory->get_order($a2cData['order']['id']);

            if (isset($a2cData['order']['notify_customer']) && $a2cData['order']['notify_customer'] === false) {
                $disableEmails = function () {
                    return false;
                };

                add_filter('woocommerce_email_enabled_customer_completed_order', $disableEmails, 100, 0);
                add_filter('woocommerce_email_enabled_customer_invoice', $disableEmails, 100, 0);
                add_filter('woocommerce_email_enabled_customer_note', $disableEmails, 100, 0);
                add_filter('woocommerce_email_enabled_customer_on_hold_order', $disableEmails, 100, 0);
                add_filter('woocommerce_email_enabled_customer_processing_order', $disableEmails, 100, 0);
                add_filter('woocommerce_email_enabled_customer_refunded_order', $disableEmails, 100, 0);
            }

            if (isset($a2cData['order']['status']['id'])) {
                $entity->set_status(
                    $a2cData['order']['status']['id'],
                    isset($a2cData['order']['status']['transition_note']) ? $a2cData['order']['status']['transition_note'] : '',
                    true
                );
            }

            if (isset($a2cData['order']['completed_date'])) {
                $entity->set_date_completed($a2cData['order']['completed_date']);
            }

            if (isset($a2cData['order']['admin_comment'])) {
                wp_set_current_user($a2cData['order']['admin_comment']['admin_user_id']);
                $entity->add_order_note($a2cData['order']['admin_comment']['text'], 1);
            }

            if (isset($a2cData['order']['customer_note'])) {
                $entity->set_customer_note($a2cData['order']['customer_note']);
            }

            if (isset($a2cData['order']['admin_private_comment'])) {
                wp_set_current_user($a2cData['order']['admin_private_comment']['admin_user_id']);
                $entity->add_order_note($a2cData['order']['admin_private_comment']['text'], 0, true);
            }

            $entity->save();

            $response['result'] = true;

        } catch (Exception $e) {
            return $reportError($e);
        } catch (Throwable $e) {
            return $reportError($e);
        }

        return $response;
    }

}

/**
 * Class M1_Config_Adapter_WebAsyst
 */
class M1_Config_Adapter_WebAsyst extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_WebAsyst constructor.
     */
    public function __construct()
    {
        $config = simplexml_load_file(M1_STORE_BASE_DIR . 'kernel/wbs.xml');

        $dbKey = (string)$config->FRONTEND['dbkey'];

        $config = simplexml_load_file(M1_STORE_BASE_DIR . 'dblist'. '/' . strtoupper($dbKey) . '.xml');

        $host = (string)$config->DBSETTINGS['SQLSERVER'];

        $this->setHostPort($host);
        $this->dbname = (string)$config->DBSETTINGS['DB_NAME'];
        $this->username = (string)$config->DBSETTINGS['DB_USER'];
        $this->password = (string)$config->DBSETTINGS['DB_PASSWORD'];

        $this->imagesDir = 'published/publicdata/'.strtoupper($dbKey).'/attachments/SC/products_pictures';
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (isset($config->VERSIONS['SYSTEM'])) {
            $this->cartVars['dbVersion'] = (string)$config->VERSIONS['SYSTEM'];
        }
    }

}

/**
 * Class M1_Config_Adapter_Virtuemart113
 */
class M1_Config_Adapter_Virtuemart113 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Virtuemart113 constructor.
     */
    public function __construct()
    {
        require_once M1_STORE_BASE_DIR . "/configuration.php";

        if (class_exists("JConfig")) {

            $jconfig = new JConfig();

            $this->setHostPort($jconfig->host);
            $this->dbname   = $jconfig->db;
            $this->username = $jconfig->user;
            $this->password = $jconfig->password;
            $this->timeZone = $jconfig->offset;
            $this->tblPrefix = $jconfig->dbprefix;
        } else {

            $this->setHostPort($mosConfig_host);
            $this->dbname   = $mosConfig_db;
            $this->username = $mosConfig_user;
            $this->password = $mosConfig_password;
        }

        if (file_exists(M1_STORE_BASE_DIR . "/administrator/components/com_virtuemart/version.php")) {
            $ver = file_get_contents(M1_STORE_BASE_DIR . "/administrator/components/com_virtuemart/version.php");
            if (preg_match('/\$RELEASE.+\'(.+)\'/', $ver, $match) != 0) {
                $this->cartVars['dbVersion'] = $match[1];
                unset($match);
            }
        }

        $this->imagesDir = "components/com_virtuemart/shop_image";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (is_dir( M1_STORE_BASE_DIR . 'images/stories/virtuemart/product')) {
            $this->imagesDir = 'images/stories/virtuemart';
            $this->productsImagesDir      = $this->imagesDir . '/product';
            $this->categoriesImagesDir    = $this->imagesDir . '/category';
            $this->manufacturersImagesDir  = $this->imagesDir . '/manufacturer';
        }
    }

}


/**
 * Class M1_Config_Adapter_SSFree
 */
class M1_Config_Adapter_SSFree extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_SSFree constructor.
     */
    public function __construct()
    {
        $config = file_get_contents(M1_STORE_BASE_DIR . 'cfg/connect.inc.php');
        preg_match("/define\(\'DB_NAME\', \'(.+)\'\);/", $config, $match);
        $this->dbname   = $match[1];
        preg_match("/define\(\'DB_USER\', \'(.+)\'\);/", $config, $match);
        $this->username = $match[1];
        preg_match("/define\(\'DB_PASS\', \'(.*)\'\);/", $config, $match);
        $this->password = $match[1];
        preg_match("/define\(\'DB_HOST\', \'(.+)\'\);/", $config, $match);
        $this->setHostPort( $match[1] );

        $this->imagesDir = "products_pictures/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        $generalInc = file_get_contents(M1_STORE_BASE_DIR . 'cfg/general.inc.php');

        preg_match("/define\(\'CONF_CURRENCY_ISO3\', \'(.+)\'\);/", $generalInc, $match);
        if (count($match) != 0) {
            $this->cartVars['iso3Currency'] = $match[1];
        }

        preg_match("/define\(\'CONF_CURRENCY_ID_LEFT\', \'(.+)\'\);/", $generalInc, $match);
        if (count($match) != 0) {
            $this->cartVars['currencySymbolLeft'] = $match[1];
        }

        preg_match("/define\(\'CONF_CURRENCY_ID_RIGHT\', \'(.+)\'\);/", $generalInc, $match);
        if (count($match) != 0) {
            $this->cartVars['currencySymbolRight'] = $match[1];
        }
    }

}

/**
 * Class M1_Config_Adapter_Ubercart
 */
class M1_Config_Adapter_Ubercart extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Ubercart constructor.
     */
    public function __construct()
    {
        @include_once M1_STORE_BASE_DIR . "sites/default/settings.php";

        $url = parse_url($db_url);

        $url['user'] = urldecode($url['user']);
        // Test if database url has a password.
        $url['pass'] = isset($url['pass']) ? urldecode($url['pass']) : '';
        $url['host'] = urldecode($url['host']);
        $url['path'] = urldecode($url['path']);
        // Allow for non-standard MySQL port.
        if (isset($url['port'])) {
            $url['host'] = $url['host'] .':'. $url['port'];
        }

        $this->setHostPort( $url['host'] );
        $this->dbname   = ltrim( $url['path'], '/' );
        $this->username = $url['user'];
        $this->password = $url['pass'];

        $this->imagesDir = "/sites/default/files/";
        if (!file_exists(M1_STORE_BASE_DIR . $this->imagesDir)) {
            $this->imagesDir = "/files";
        }

        if (file_exists(M1_STORE_BASE_DIR . "/modules/ubercart/uc_cart/uc_cart.info")) {
            $str = file_get_contents(M1_STORE_BASE_DIR . "/modules/ubercart/uc_cart/uc_cart.info");
            if (preg_match('/version\s+=\s+".+-(.+)"/', $str, $match) != 0) {
                $this->cartVars['dbVersion'] = $match[1];
                unset($match);
            }
        }

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }

}



/**
 * Class M1_Config_Adapter_Tomatocart
 */
class M1_Config_Adapter_Tomatocart extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Tomatocart constructor.
     */
    public function __construct()
    {
        $config = file_get_contents(M1_STORE_BASE_DIR . "includes/configure.php");
        preg_match("/define\(\'DB_DATABASE\', \'(.+)\'\);/", $config, $match);
        $this->dbname   = $match[1];
        preg_match("/define\(\'DB_SERVER_USERNAME\', \'(.+)\'\);/", $config, $match);
        $this->username = $match[1];
        preg_match("/define\(\'DB_SERVER_PASSWORD\', \'(.*)\'\);/", $config, $match);
        $this->password = $match[1];
        preg_match("/define\(\'DB_SERVER\', \'(.+)\'\);/", $config, $match);
        $this->setHostPort( $match[1] );

        preg_match("/define\(\'DIR_WS_IMAGES\', \'(.+)\'\);/", $config, $match);
        $this->imagesDir = $match[1];

        preg_match("/define\(\'DB_TABLE_PREFIX\', \'(.+)\'\);/", $config, $match);
        if (isset($match[1]))
            $this->tblPrefix = $match[1];

        $this->categoriesImagesDir    = $this->imagesDir.'categories/';
        $this->productsImagesDir      = $this->imagesDir.'products/';
        $this->manufacturersImagesDir = $this->imagesDir . 'manufacturers/';
        if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'application_top.php')) {
            $conf = file_get_contents (M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "application_top.php");
            preg_match("/define\('PROJECT_VERSION.*/", $conf, $match);

            if (isset($match[0]) && !empty($match[0])) {
                preg_match("/\d.*/", $match[0], $project);
                if (isset($project[0]) && !empty($project[0])) {
                    $version = $project[0];
                    $version = str_replace(array(" ","-","_","'",");"), "", $version);
                    if ($version != '') {
                        $this->cartVars['dbVersion'] = strtolower($version);
                    }
                }
            } else {
                //if another version
                if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'version.php')) {
                    @require_once M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "version.php";
                    if (defined('PROJECT_VERSION') && PROJECT_VERSION != '' ) {
                        $version = PROJECT_VERSION;
                        preg_match("/\d.*/", $version, $vers);
                        if (isset($vers[0]) && !empty($vers[0])) {
                            $version = $vers[0];
                            $version = str_replace(array(" ","-","_"), "", $version);
                            if ($version != '') {
                                $this->cartVars['dbVersion'] = strtolower($version);
                            }
                        }
                    }
                }
            }
        }
    }

}



/**
 * Class M1_Config_Adapter_Sunshop4
 */
class M1_Config_Adapter_Sunshop4 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Sunshop4 constructor.
     */
    public function __construct()
    {
        @require_once M1_STORE_BASE_DIR
            . "include" . DIRECTORY_SEPARATOR
            . "config.php";

        $this->imagesDir = "images/products/";

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (defined('ADMIN_DIR')) {
            $this->cartVars['AdminUrl'] = ADMIN_DIR;
        }

        $this->setHostPort($servername);
        $this->username  = $dbusername;
        $this->password  = $dbpassword;
        $this->dbname    = $dbname;

        if (isset($dbprefix)) {
            $this->tblPrefix = $dbprefix;
        }

        $version = $this->getCartVersionFromDb("value", "settings", "name = 'version'");
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }
    }

}



/**
 * Class miSettings
 */
class miSettings {

    protected $_arr;

    /**
     * @return miSettings|null
     */
    public function singleton()
    {
        static $instance = null;
        if ($instance == null) {
            $instance = new miSettings();
        }
        return $instance;
    }

    /**
     * @param $arr
     */
    public function setArray($arr)
    {
        $this->_arr[] = $arr;
    }

    /**
     * @return mixed
     */
    public function getArray()
    {
        return $this->_arr;
    }

}

/**
 * Class M1_Config_Adapter_Summercart3
 */
class M1_Config_Adapter_Summercart3 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Summercart3 constructor.
     */
    public function __construct()
    {
        @include_once M1_STORE_BASE_DIR . "include/miphpf/Config.php";

        $miSettings = new miSettings();
        $instance = $miSettings->singleton();

        $data = $instance->getArray();

        $this->setHostPort($data[0]['MI_DEFAULT_DB_HOST']);
        $this->dbname   = $data[0]['MI_DEFAULT_DB_NAME'];
        $this->username = $data[0]['MI_DEFAULT_DB_USER'];
        $this->password = $data[0]['MI_DEFAULT_DB_PASS'];
        $this->imagesDir = "/userfiles/";

        $this->categoriesImagesDir    = $this->imagesDir . "categoryimages";
        $this->productsImagesDir      = $this->imagesDir . "productimages";
        $this->manufacturersImagesDir = $this->imagesDir . "manufacturer";

        if (file_exists(M1_STORE_BASE_DIR . "/include/VERSION")) {
            $indexFileContent = file_get_contents(M1_STORE_BASE_DIR . "/include/VERSION");
            $this->cartVars['dbVersion'] = trim($indexFileContent);
        }
    }

}



/**
 * Class M1_Config_Adapter_Squirrelcart242
 */
class M1_Config_Adapter_Squirrelcart242 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Squirrelcart242 constructor.
     */
    public function __construct()
    {
        include_once(M1_STORE_BASE_DIR . 'squirrelcart/config.php');

        $this->setHostPort($sql_host);
        $this->dbname      = $db;
        $this->username    = $sql_username;
        $this->password    = $sql_password;

        $this->imagesDir                 = $img_path;
        $this->categoriesImagesDir       = $img_path . "/categories";
        $this->productsImagesDir         = $img_path . "/products";
        $this->manufacturersImagesDir    = $img_path;

        $version = $this->getCartVersionFromDb("DB_Version", "Store_Information", "record_number = 1");
        if ($version != '' ) {
            $this->cartVars['dbVersion'] = $version;
        }
    }
}

/**
 * Class M1_Config_Adapter_Shopware
 */
class M1_Config_Adapter_Shopware extends M1_Config_Adapter
{
    /**
     * M1_Config_Adapter_Shopware constructor.
     */
    public function __construct()
    {
        if (file_exists(M1_STORE_BASE_DIR  . 'engine/Shopware/Application.php')) { //shopware version < 6
            $file = file_get_contents(M1_STORE_BASE_DIR  . 'engine/Shopware/Application.php');
            if (preg_match('/const\s+VERSION\s*=\s*[\'"]([0-9.]+)[\'"]/', $file, $matches) && isset($matches[1])) {
                $this->cartVars['dbVersion'] = $matches[1];
            }

            if (!$this->cartVars['dbVersion']) {
                if (file_exists(M1_STORE_BASE_DIR  . 'engine/Shopware/Kernel.php')) {
                    $file = file_get_contents(M1_STORE_BASE_DIR  . 'engine/Shopware/Kernel.php');
                    if (preg_match('/\'version\'\s*=>\s*\'([0-9.]+)\'/', $file, $matches) && isset($matches[1])) {
                        $this->cartVars['dbVersion'] = $matches[1];
                    }
                }
            }

            $environment = getenv('SHOPWARE_ENV') ?: getenv('REDIRECT_SHOPWARE_ENV') ?: 'production';
            $timeZone = null;
            if (file_exists(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/config_' . $environment . '.php')) {
                $file  = file_get_contents(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/config_' . $environment . '.php');
                if (preg_match('/["\']date\.timezone["\']\s?=>\s?["\'](.*)?["\']/', $file, $matches) && isset($matches[1])) {
                    $timeZone = $matches[1];
                }
            }

            if (!$timeZone && file_exists(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/config.php')) {
                $file  = file_get_contents(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/config.php');
                if (preg_match('/["\']date\.timezone["\']\s?=>\s?["\'](.*)?["\']/', $file, $matches) && isset($matches[1])) {
                    $timeZone = $matches[1];
                }
            }

            if (!$timeZone && file_exists(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/Default.php')) {
                $file  = file_get_contents(M1_STORE_BASE_DIR  . 'engine/Shopware/Configs/Default.php');
                if (preg_match('/["\']date\.timezone["\']\s?=>\s?["\'](.*)?["\']/', $file, $matches) && isset($matches[1])) {
                    $timeZone = $matches[1];
                }
            } else {
                try {
                    $timeZone = date_default_timezone_get();
                } catch (Exception $e) {
                    $timeZone = 'UTC';
                }
            }

            $configs = include(M1_STORE_BASE_DIR . "config.php");
            $this->setHostPort($configs['db']['host']);
            $this->username = $configs['db']['username'];
            $this->password = $configs['db']['password'];
            $this->dbname   = $configs['db']['dbname'];
            $this->tblPrefix = 's_';
            $this->timeZone = $timeZone;
        } else {
            if (file_exists(M1_STORE_BASE_DIR . 'vendor/autoload.php')) {
                $m1StoreBaseDir = M1_STORE_BASE_DIR;
            } else {
                $m1StoreBaseDir = realpath(M1_STORE_BASE_DIR . '..') . DIRECTORY_SEPARATOR;
            }

            require $m1StoreBaseDir . 'vendor/autoload.php';

            $shopwareVersion = $composerFile = '';

            if (class_exists('PackageVersions\Versions')) {
                preg_match('/(?:v)?\s*((?:[0-9]+\.?)+)/', \PackageVersions\Versions::getVersion('shopware/core'), $matches);
            } elseif ((class_exists('Composer\InstalledVersions'))) {
                preg_match('/(?:v)?\s*((?:[0-9]+\.?)+)/', \Composer\InstalledVersions::getVersion('shopware/core'), $matches);
            }


            if (isset($matches[1])) {
                $shopwareVersion = $matches[1];
            } elseif (file_exists(M1_STORE_BASE_DIR . 'composer.json')) {
                $composerFile = file_get_contents(M1_STORE_BASE_DIR . 'composer.json');
            } elseif (file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'composer.json')) {
                $composerFile = file_get_contents(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'composer.json');
            }

            if ($composerFile) {
                $content = json_decode($composerFile, true);
                $shopwareVersion = str_replace(['~', '^', 'v'], '', isset($content['require']['shopware/core']) ? $content['require']['shopware/core'] : '');
            }

            if (empty($shopwareVersion)) {
                die('ERROR_DETECTING_PLATFORM_VERSION');
            }

            $this->cartVars['dbVersion'] = $shopwareVersion;
            $this->timeZone = 'UTC';

            $envLoader = new Symfony\Component\Dotenv\Dotenv();
            $config = $envLoader->parse(file_get_contents($m1StoreBaseDir . '.env'));

            $params = [];
            foreach (parse_url($config['DATABASE_URL']) as $param => $value) {
                if (is_string($value)) {
                    $params[$param] = rawurldecode($value);
                } else {
                    $params[$param] = $value;
                }
            }

            $this->cartVars['sdn_strategy'] = isset($config['SHOPWARE_CDN_STRATEGY_DEFAULT']) ? $config['SHOPWARE_CDN_STRATEGY_DEFAULT'] : 'id';

            $port = isset($params['port']) ? ':' . $params['port'] : '';
            $this->setHostPort($params['host'] . $port);

            $this->dbname = ltrim($params['path'], '/');
            $this->username = isset($params['user']) ? $params['user'] : '';
            $this->password = isset($params['pass']) ? $params['pass'] : '';
        }
    }

    /**
     * @param array $data Contain request params and payload
     *
     * @return mixed
     * @throws Exception
     */
    public function productAddAction(array $data)
    {
        return $this->_importEntity($data);
    }

    /**
     * @param array $a2cData Contain request params and payload
     *
     * @return mixed
     * @throws Exception
     */
    public function productUpdateAction(array $a2cData)
    {
        return $this->_importEntity($a2cData);
    }

    /**
     * @param array $a2cData Contain request params and payload
     *
     * @return mixed
     * @throws Exception
     */
    public function apiSend(array $a2cData)
    {
        return $this->_importEntity($a2cData);
    }

    /**
     * @param array $data Data to import
     *
     * @return array
     */
    private function _importEntity($data)
    {
        $response = array('error' => null, 'data' => false);
        try {
            if (file_exists(M1_STORE_BASE_DIR . '.env')) {
                (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '.env');
                $storeRoot = M1_STORE_BASE_DIR;
            } elseif(file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env')) {
                (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env');
                $storeRoot = M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR;
            } else {
                throw new \Exception('File \'.env\' not found');
            }

            require $storeRoot . 'vendor/autoload.php';

            if (function_exists('curl_version')) {
                $response['data'] = $this->_sendRequestWithCurl($data);
            } elseif (class_exists('\GuzzleHttp\Client')) {
                $response['data'] = $this->_sendRequestWithGuzzle($data);
            } else {
                throw new Exception('Http client not found');
            }

        } catch (Exception $e) {
            $response['error']['message'] = $e->getMessage();
            $response['error']['code'] = $e->getCode();
        }

        return $response;
    }

    /**
     * @param array $data Contain request params and payload
     *
     * @return bool
     * @throws Exception
     */
    private function _sendRequestWithCurl($data)
    {
        $shopwareVersion = $this->cartVars['dbVersion'];
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $this->_getToken($data['meta']['user_id'])
        );

        $ch = curl_init();

        try {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        } catch (\Exception $e) {
            $scriptName = 'index.php';
        }

        if (version_compare($shopwareVersion, '6.4.0.0', '>=')) {
            $apiVersion = '/api/';
        } else {
            $apiVersion = '/api/v2/';
        }

        $uri = $_SERVER['SERVER_NAME'] . str_replace('/' . $scriptName, '', $_SERVER['PHP_SELF']);
        $uri = str_replace('/bridge2cart', '', $uri);

        if ($data['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_URL, $uri . $apiVersion . $data['entity']);
        } elseif ($data['method'] === 'DELETE') {
            curl_setopt($ch, CURLOPT_URL, $uri . $apiVersion . $data['entity']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } else {
            $url = $uri . $apiVersion . $data['entity'] . '/' . $data['meta']['entity_id'];
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data['payload']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = json_decode(substr($res, curl_getinfo($ch, CURLINFO_HEADER_SIZE)));

        curl_close($ch);

        if ($httpCode == "204") {
            return true;
        } else {
            $message = '';
            if (isset($body->errors[0]->detail)) {
                $message = 'Error message: ' . $body->errors[0]->detail;
            }

            throw new Exception('Bridge curl failed. Not expected http code. ' . $message, $httpCode);
        }
    }

    /**
     * @param array $data Contain request params and payload
     *
     * @return bool
     * @throws Exception
     */
    private function _sendRequestWithGuzzle($data)
    {
        $shopwareVersion = $this->cartVars['dbVersion'];
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->_getToken($data['meta']['user_id'])
        );

        $client = new \GuzzleHttp\Client();
        $message = '';

        try {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
        } catch (\Exception $e) {
            $scriptName = 'index.php';
        }

        if (version_compare($shopwareVersion, '6.4.0.0', '>=')) {
            $apiVersion = '/api/';
        } else {
            $apiVersion = '/api/v2/';
        }

        $uri = $_SERVER['SERVER_NAME'] . str_replace('/' . $scriptName, '', $_SERVER['PHP_SELF']);
        $uri = str_replace('/bridge2cart', '', $uri);

        try {
            $options = ['body' => $data['payload'], 'headers' => $headers];

            if ($data['method'] === 'POST') {
                $response = $client->post(
                    $uri . $apiVersion . $data['entity'],
                    $options
                );
            } elseif ($data['method'] === 'DELETE') {
                $response = $client->delete(
                    $uri . $apiVersion . $data['entity']
                );
            } else {
                $response = $client->put(
                    $uri . $apiVersion . $data['entity'] . '/' . $data['meta']['entity_id'],
                    $options
                );
            }
        } catch (GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = json_decode(((string)$response->getBody()), true);

                if (isset($body['errors'][0]['detail'])) {
                    $message = 'Error message: ' . $body['errors'][0]['detail'];
                }
            } else {
                throw new Exception('Guzzle failed');
            }
        }

        if ($response->getStatusCode() === 204) {
            return true;
        } else {
            throw new Exception('Not expected http code from shopware. ' . $message, $response->getStatusCode());
        }
    }

    /**
     * @param string $userId Admin user id
     *
     * @return string
     * @throws Exception
     */
    private function _getToken($userId)
    {
        $shopwareVersion = $this->cartVars['dbVersion'];

        if (version_compare($shopwareVersion, '6.4.0.0', '>=')) {
            $dateInterval = (new \DateTimeImmutable())->add(new \DateInterval('PT1H'));
        } else {
            $dateInterval = (new DateTime())->add(new DateInterval('PT1H'));
        }

        if (file_exists(M1_STORE_BASE_DIR . 'config/jwt/private.pem')) {
            $storeRoot = M1_STORE_BASE_DIR;
        } elseif (file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'config/jwt/private.pem')) {
            $storeRoot = M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR;
        } else {
            throw new Exception('File \'private.pem\' not found');
        }

        $connection = \Shopware\Core\Kernel::getConnection();
        $client = new \Shopware\Core\Framework\Api\OAuth\Client\ApiClient('administration', true);

        $scopeRepo = new \Shopware\Core\Framework\Api\OAuth\ScopeRepository(array(), $connection);
        $finalizedScopes = $scopeRepo->finalizeScopes(array(), 'password', $client, $userId);

        $tokenRepo = new \Shopware\Core\Framework\Api\OAuth\AccessTokenRepository();
        $accessToken = $tokenRepo->getNewToken($client, $finalizedScopes, $userId);

        $writeScope = new \Shopware\Core\Framework\Api\OAuth\Scope\WriteScope();
        $accessToken->setClient($client);
        $accessToken->setUserIdentifier($userId);
        $accessToken->addScope($writeScope);
        $accessToken->setExpiryDateTime($dateInterval);

        $key = new \League\OAuth2\Server\CryptKey($storeRoot . 'config/jwt/private.pem', 'shopware', false);

        if (version_compare($shopwareVersion, '6.4.0.0', '>=')) {
            $accessToken->setPrivateKey($key);

            return 'Bearer ' . (string)$accessToken->__toString();
        } else {
            return 'Bearer ' . (string)$accessToken->convertToJWT($key);
        }
    }
}

/**
 * Class M1_Config_Adapter_SSPremium
 */
class M1_Config_Adapter_SSPremium extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_SSPremium constructor.
     */
    public function __construct()
    {
        if (file_exists(M1_STORE_BASE_DIR . 'cfg/connect.inc.php')) {
            $config = file_get_contents(M1_STORE_BASE_DIR . 'cfg/connect.inc.php');
            preg_match("/define\(\'DB_NAME\', \'(.+)\'\);/", $config, $match);
            $this->dbname   = $match[1];
            preg_match("/define\(\'DB_USER\', \'(.+)\'\);/", $config, $match);
            $this->username = $match[1];
            preg_match("/define\(\'DB_PASS\', \'(.*)\'\);/", $config, $match);
            $this->password = $match[1];
            preg_match("/define\(\'DB_HOST\', \'(.+)\'\);/", $config, $match);
            $this->setHostPort( $match[1] );

            $this->imagesDir = "products_pictures/";
            $this->categoriesImagesDir    = $this->imagesDir;
            $this->productsImagesDir      = $this->imagesDir;
            $this->manufacturersImagesDir = $this->imagesDir;

            $version = $this->getCartVersionFromDb("value", "SS_system", "varName = 'version_number'");
            if ($version != '') {
                $this->cartVars['dbVersion'] = $version;
            }
        } else {
            $config = include M1_STORE_BASE_DIR . "wa-config/db.php";
            $this->dbname   = $config['default']['database'];
            $this->username = $config['default']['user'];
            $this->password = $config['default']['password'];
            $this->setHostPort($config['default']['host']);

            $this->imagesDir = "products_pictures/";
            $this->categoriesImagesDir    = $this->imagesDir;
            $this->productsImagesDir      = $this->imagesDir;
            $this->manufacturersImagesDir = $this->imagesDir;
            $this->cartVars['dbVersion'] = '5.0';
        }

    }

}

/**
 * Class M1_Config_Adapter_Ubercart3
 */
class M1_Config_Adapter_Ubercart3 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Ubercart3 constructor.
     */
    public function __construct()
    {
        @include_once M1_STORE_BASE_DIR . "sites/default/settings.php";

        $url = $databases['default']['default'];

        $url['username'] = urldecode($url['username']);
        $url['password'] = isset($url['password']) ? $url['password'] : '';
        $url['database'] = urldecode($url['database']);
        if (isset($url['port'])) {
            $url['host'] = $url['host'] .':'. $url['port'];
        }

        $this->setHostPort( $url['host'] );
        $this->dbname   = ltrim( $url['database'], '/' );
        $this->username = $url['username'];
        $this->password = $url['password'];

        $this->imagesDir = "/sites/default/files/";
        if (!file_exists( M1_STORE_BASE_DIR . $this->imagesDir )) {
            $this->imagesDir = "/files";
        }

        $fileInfo = '';

        if (file_exists(M1_STORE_BASE_DIR . '/modules/ubercart/uc_cart/uc_cart.info')) {
            $fileInfo = M1_STORE_BASE_DIR . '/modules/ubercart/uc_cart/uc_cart.info';
        } elseif (file_exists(M1_STORE_BASE_DIR . '/sites/all/modules/ubercart/uc_cart/uc_cart.info')) {
            $fileInfo = M1_STORE_BASE_DIR . '/sites/all/modules/ubercart/uc_cart/uc_cart.info';
        }

        if (file_exists( $fileInfo )) {
            $str = file_get_contents( $fileInfo );
            if (preg_match('/version\s+=\s+".+-(.+)"/', $str, $match) != 0) {
                $this->cartVars['dbVersion'] = $match[1];
                unset($match);
            }
        }

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }

}


/**
 * Class M1_Config_Adapter_Prestashop
 */
class M1_Config_Adapter_Prestashop extends M1_Config_Adapter
{

    public function __construct()
    {
        if (!file_exists(M1_STORE_BASE_DIR . "app/AppKernel.php")) {
            require_once(M1_STORE_BASE_DIR . "config/settings.inc.php");
        }

        if (file_exists(M1_STORE_BASE_DIR . "config/defines.inc.php"))
            require_once(M1_STORE_BASE_DIR . "config/defines.inc.php");
        if (file_exists(M1_STORE_BASE_DIR . "config/autoload.php"))
            require_once(M1_STORE_BASE_DIR . "config/autoload.php");

        if (file_exists(M1_STORE_BASE_DIR . "config/bootstrap.php"))
            require_once(M1_STORE_BASE_DIR . "config/bootstrap.php");

        $this->setHostPort(_DB_SERVER_);
        $this->dbname = _DB_NAME_;
        $this->username = _DB_USER_;
        $this->password = _DB_PASSWD_;
        $this->tblPrefix = _DB_PREFIX_;

        if (defined('_PS_IMG_DIR_')) {
            $this->imagesDir = DIRECTORY_SEPARATOR . str_replace(M1_STORE_BASE_DIR, '', _PS_IMG_DIR_);
        } else {
            $this->imagesDir = "/img/";
        }

        if (defined('_PS_VERSION_')) {
            $this->cartVars['dbVersion'] = _PS_VERSION_;
        }

        $this->categoriesImagesDir = DIRECTORY_SEPARATOR . str_replace(M1_STORE_BASE_DIR, '', _PS_CAT_IMG_DIR_);
        $this->productsImagesDir = DIRECTORY_SEPARATOR . str_replace(M1_STORE_BASE_DIR, '', _PS_PROD_IMG_DIR_);
        $this->manufacturersImagesDir = DIRECTORY_SEPARATOR . str_replace(M1_STORE_BASE_DIR, '', _PS_MANU_IMG_DIR_);
    }

    /**
     * @inheritDoc
     */
    public function sendEmailNotifications(array $a2cData)
    {
        require_once _PS_ROOT_DIR_ . '/config/config.inc.php';

        if (file_exists(_PS_ROOT_DIR_ . '/vendor/autoload.php')) {
            require_once _PS_ROOT_DIR_ . '/vendor/autoload.php';
        }

        if (version_compare($this->cartVars['dbVersion'], '1.7.0', '<')) {
            $moduleName = 'mailalerts';
            $className = 'MailAlerts';
        } else {
            global $kernel;
            if(!$kernel){
                require_once _PS_ROOT_DIR_.'/app/AppKernel.php';
                $kernel = new AppKernel('prod', false);
                $kernel->boot();
            }

            $className = 'Ps_EmailAlerts';
            $moduleName = 'ps_emailalerts';
        }

        $path = _PS_ROOT_DIR_ . '/modules/' . $moduleName . '/' . $moduleName . '.php';

        if (file_exists($path)) {
            require_once $path;

            $context = Context::getContext();
            $context->shop = new Shop($a2cData['store_id']);
            Shop::setContext(Shop::CONTEXT_SHOP, $a2cData['store_id']);
            Configuration::loadConfiguration();

            $module = new $className();

            foreach ($a2cData['notifications'] as $notification) {
                $order = new Order($notification['order_id']);
                $customer = new Customer((int)$order->id_customer);
                $cart = new Cart((int)$order->id_cart);
                $currency = new Currency((int)$order->id_currency);

                if (!$context->currency) {
                    $context->currency = $currency;
                }

                $orderStatus = OrderHistoryCore::getLastOrderState((int)$order->id);

                $module->hookActionValidateOrder(
                    array(
                        'cart' => $cart,
                        'customer' => $customer,
                        'order' => $order,
                        'currency' => $currency,
                        'orderStatus' => $orderStatus,
                    )
                );
            }

            return true;
        }

        return false;
    }

}



/**
 * Class M1_Config_Adapter_Oxid
 */
class M1_Config_Adapter_Oxid extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Oxid constructor.
     */
    public function __construct()
    {
        //@include_once M1_STORE_BASE_DIR . "config.inc.php";
        $config = file_get_contents(M1_STORE_BASE_DIR . "config.inc.php");
        try {
            preg_match("/dbName(.+)?=(.+)?\'(.+)\';/", $config, $match);
            $this->dbname   = $match[3];
            preg_match("/dbUser(.+)?=(.+)?\'(.+)\';/", $config, $match);
            $this->username = $match[3];
            preg_match("/dbPwd(.+)?=(.+)?\'(.+)\';/", $config, $match);
            $this->password = isset($match[3]) ? $match[3] : '';
            preg_match("/dbHost(.+)?=(.+)?\'(.*)\';/", $config, $match);
            $this->setHostPort($match[3]);
        } catch (Exception $e) {
            die('ERROR_READING_STORE_CONFIG_FILE');
        }

        preg_match('/date_default_timezone_set\s*\(\s*[\'"](.*)[\'"]\s*\)/', $config, $timezone);
        if (isset($timezone[1])) {
            $this->cartVars['timezone'] = $timezone[1];
        }

        //check about last slash
        $this->imagesDir = "out/pictures/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (is_file(M1_STORE_BASE_DIR . '/core/oxconfk.php')) {
            $keyConfigFile = file_get_contents(M1_STORE_BASE_DIR . '/core/oxconfk.php');
            preg_match("/sConfigKey(.+)?=(.+)?\"(.+)?\";/", $keyConfigFile, $match);//$match[3]
            $keyConfVal = $match[3];
        } else {
            $keyConfigFile = file_get_contents(M1_STORE_BASE_DIR . '..' . '/vendor/oxid-esales/oxideshop-ce/source/Core/Config.php');
            preg_match('/DEFAULT_CONFIG_KEY\s*=\s*[\'"](.+?)[\'"]\s*/', $keyConfigFile, $match);
            $keyConfVal = $match[1];
        }

        $this->cartVars['sConfigKey'] = $keyConfVal;

        $version = $this->getCartVersionFromDb("OXVERSION", "oxshops", "OXACTIVE=1 LIMIT 1" );
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }
    }

}



/**
 * Class M1_Config_Adapter_Oscommerce3
 */
class M1_Config_Adapter_Oscommerce3 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Oscommerce3 constructor.
     */
    public function __construct()
    {
        $file = M1_STORE_BASE_DIR .'/osCommerce/OM/Config/settings.ini';
        $settings = parse_ini_file($file);
        $this->imagesDir = "/public/";
        $this->categoriesImagesDir    = $this->imagesDir."/categories";
        $this->productsImagesDir      = $this->imagesDir."/products";
        $this->manufacturersImagesDir = $this->imagesDir;

        $this->host      = $settings['db_server'];
        $this->setHostPort($settings['db_server_port']);
        $this->username  = $settings['db_server_username'];
        $this->password  = $settings['db_server_password'];
        $this->dbname    = $settings['db_database'];

        if (isset($settings['db_table_prefix']))
            $this->tblPrefix = $settings['db_table_prefix'];
    }

}



/**
 * Class M1_Config_Adapter_Oscommerce22ms2
 */
class M1_Config_Adapter_Oscommerce22ms2 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Oscommerce22ms2 constructor.
     */
    public function __construct()
    {
        $curDir = getcwd();

        chdir(M1_STORE_BASE_DIR);

        @require_once M1_STORE_BASE_DIR
            . "includes" . DIRECTORY_SEPARATOR
            . "configure.php";

        chdir($curDir);

        $this->imagesDir = DIR_WS_IMAGES;

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        if (defined('DIR_WS_PRODUCT_IMAGES') ) {
            $this->productsImagesDir = DIR_WS_PRODUCT_IMAGES;
        }
        if (defined('CFG_TIME_ZONE') ) {
            $this->cartVars['timeZone'] = CFG_TIME_ZONE;
        }
        if (defined('DIR_WS_ORIGINAL_IMAGES')) {
            $this->productsImagesDir = DIR_WS_ORIGINAL_IMAGES;
        }
        $this->manufacturersImagesDir = $this->imagesDir;

        //$this->Host      = DB_SERVER;
        $this->setHostPort(DB_SERVER);
        $this->username  = DB_SERVER_USERNAME;
        $this->password  = DB_SERVER_PASSWORD;
        $this->dbname    = DB_DATABASE;

        if (defined('DB_TABLE_PREFIX'))
            $this->tblPrefix = DB_TABLE_PREFIX;

        chdir(M1_STORE_BASE_DIR);
        if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'application_top.php')) {
            $conf = file_get_contents (M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "application_top.php");
            preg_match("/define\('PROJECT_VERSION.*/", $conf, $match);
            if (isset($match[0]) && !empty($match[0])) {
                preg_match("/\d.*/", $match[0], $project);
                if (isset($project[0]) && !empty($project[0])) {
                    $version = $project[0];
                    $version = str_replace(array(" ","-","_","'",");"), "", $version);
                    if ($version != '') {
                        $this->cartVars['dbVersion'] = strtolower($version);
                    }
                }
            } else {
                //if another oscommerce based cart
                if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'version.php')) {
                    @require_once M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "version.php";
                    if (defined('PROJECT_VERSION') && PROJECT_VERSION != '' ) {
                        $version = PROJECT_VERSION;
                        preg_match("/\d.*/", $version, $vers);
                        if (isset($vers[0]) && !empty($vers[0])) {
                            $version = $vers[0];
                            $version = str_replace(array(" ","-","_"), "", $version);
                            if ($version != '') {
                                $this->cartVars['dbVersion'] = strtolower($version);
                            }
                        }
                        //if zen_cart
                    } else {
                        if (defined('PROJECT_VERSION_MAJOR') && PROJECT_VERSION_MAJOR != '' ) {
                            $this->cartVars['dbVersion'] = PROJECT_VERSION_MAJOR;
                        }
                        if (defined('PROJECT_VERSION_MINOR') && PROJECT_VERSION_MINOR != '' ) {
                            $this->cartVars['dbVersion'] .= '.' . PROJECT_VERSION_MINOR;
                        }
                    }
                }
            }
        }
        chdir($curDir);
    }

}



/**
 * Class M1_Config_Adapter_Opencart14
 */
class M1_Config_Adapter_Opencart14 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Opencart14 constructor.
     */
    public function __construct()
    {
        include_once (M1_STORE_BASE_DIR . "/config.php");

        if (defined('DB_HOST')) {
            $this->setHostPort(DB_HOST);
        } else {
            $this->setHostPort(DB_HOSTNAME);
        }

        if (defined('DB_USER')) {
            $this->username = DB_USER;
        } else {
            $this->username = DB_USERNAME;
        }

        if (defined('DB_PORT')) {
            $this->port = DB_PORT;
        }

        $this->password = DB_PASSWORD;

        if (defined('DB_NAME')) {
            $this->dbname = DB_NAME;
        } else {
            $this->dbname = DB_DATABASE;
        }

        if (defined('DB_PREFIX')) {
            $this->tblPrefix = DB_PREFIX;
        }

        $indexFileContent = '';
        $startupFileContent = '';

        if (file_exists(M1_STORE_BASE_DIR . "/index.php")) {
            $indexFileContent = file_get_contents(M1_STORE_BASE_DIR . "/index.php");
        }

        if (file_exists(M1_STORE_BASE_DIR . "/system/startup.php")) {
            $startupFileContent = file_get_contents(M1_STORE_BASE_DIR . "/system/startup.php");
        }

        if (preg_match("/define\('\VERSION\'\, \'(.+)\'\)/", $indexFileContent, $match) == 0 ) {
            preg_match("/define\('\VERSION\'\, \'(.+)\'\)/", $startupFileContent, $match);
        }

        if (count($match) > 0) {
            $this->cartVars['dbVersion'] = $match[1];
            unset($match);
        }

        $this->imagesDir              = "/image/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

    }

    /**
     * @param array $a2cData Data
     *
     * @return array|mixed
     */
    public function sendEmailNotifications(array $a2cData)
    {
        foreach ($a2cData as $notification) {
            $this->_sendNewOrderEmailNotifications(
                $notification['storeId'],
                $notification['orderId'],
                $notification['notifyCustomer'],
                $notification['commentFromAdmin'],
                $notification['notifyAdmin']
            );
        }

        return ['result' => true];
    }

    /**
     * @param int    $store_id         StoreId
     * @param int    $order_id         OrderId
     * @param bool   $notifyCustomer   Whether customer must be notified
     * @param string $commentFromAdmin Admin message to the customer
     * @param bool   $notifyAdmin      Whether admin must be notified
     */
    private function _sendNewOrderEmailNotifications($store_id, $order_id, $notifyCustomer, $commentFromAdmin, $notifyAdmin)
    {
        require_once(DIR_SYSTEM . 'startup.php');

        $registry = new Registry();

        $loader = new Loader($registry);
        $registry->set('load', $loader);

        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $registry->set('db', $db);

        $request = new Request();
        $registry->set('request', $request);

        if (version_compare($this->cartVars['dbVersion'], '2.2', '>=')) {
            // Event
            $event = new Event($registry);
            $registry->set('event', $event);
        }

        $store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE store_id = " . $store_id);

        $config = new Config();
        if (version_compare($this->cartVars['dbVersion'], '2.2', '>=')) {
            $config->load('default');
        }

        if ($store_query->num_rows) {
            $config->set('config_store_id', $store_query->row['store_id']);
        } else {
            $config->set('config_url', HTTP_SERVER);
            $config->set('config_ssl', HTTPS_SERVER);
            $config->set('config_store_id', 0);
        }

        // Settings
        $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' OR store_id = '" . $store_id . "' ORDER BY store_id ASC");

        if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
            $unserialize = function ($data) {
                return unserialize($data);
            };
        } else {
            $unserialize = function ($data) {
                return json_decode($data);
            };
        }

        foreach ($query->rows as $setting) {
            if (!$setting['serialized']) {
                $config->set($setting['key'], $setting['value']);
            } else {
                $config->set($setting['key'], $unserialize($setting['value']));
            }
        }

        if (version_compare($this->cartVars['dbVersion'], '3', '>=')) {
            $session = new Session($config->get('session_engine'), $registry);
        } else {
            $session = new Session();
        }

        $registry->set('session', $session);

        $url = new Url($config->get('config_url'), $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'));
        $registry->set('url', $url);

        $loader->model('checkout/order');

        /**
         * @var ModelCheckoutOrder $checkout
         */
        $checkout = $registry->get('model_checkout_order');
        $order_info = $checkout->getOrder($order_id);
        $config->set('config_currency', $order_info['currency_code']);
        $registry->set('config', $config);

        if (version_compare($this->cartVars['dbVersion'], '2.2', '>=')) {
            $langKey = 'language_code';
        } else {
            $langKey = 'language_directory';
        }

        $language = new Language($order_info[$langKey]);
        $language->load($order_info[$langKey]);

        if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
            $language->load($order_info['language_filename']);
            $loader->library('currency');
            $loader->library('mail');
        }

        if (version_compare($this->cartVars['dbVersion'], '3', '<')) {
            $language->load('mail/order');
            $langTextPrefix = 'text_new_';
        } else {
            $langTextPrefix = 'text_';
            $language->load('mail/order_add');
            $language->load('mail/order_alert');
        }

        $registry->set('language', $language);

        if (version_compare($this->cartVars['dbVersion'], '2.2', '>=')) {
            /**
             * @var \Cart\Currency
             */
            $currency = new \Cart\Currency($registry);
        } else {
            $currency = new Currency($registry);
        }

        $order_status_id = (int)$order_info['order_status_id'];

        $download_status = false;
        if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
            $order_product_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
            $order_download_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");
            $download_status = (bool)$order_download_query->num_rows;
        } else {
            $order_product_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
            foreach ($order_product_query->rows as $order_product) {
                // Check if there are any linked downloads
                $product_download_query = $db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

                if ($product_download_query->row['total']) {
                    $download_status = true;
                }
            }
        }

        $order_total_query = $db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
        $order_voucher_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

        // Send out order confirmation mail
        $order_status_query = $db->query(
            "SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'"
        );

        if ($order_status_query->num_rows) {
            $order_status = $order_status_query->row['name'];
        } else {
            $order_status = '';
        }

        if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
            $storeName = html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8');
            $configPrefix = '';
            $propertyPrefix = '';
        } else {
            $storeName = $order_info['store_name'];
            $configPrefix = 'mail_';
            $propertyPrefix = 'smtp_';
        }

        if (!$storeName) {
            $storeName = $config->get('config_name');
        }

        $themeDefaultDir = $config->get('theme_default_directory') ?: 'default';

        if ($notifyCustomer || $notifyAdmin) {
            $subject = sprintf($language->get($langTextPrefix . 'subject'), $storeName, $order_id);

            // HTML Mail
            $data = array();
            $data['title'] = sprintf($language->get($langTextPrefix . 'subject'), $storeName, $order_id);
            $data['text_greeting'] = sprintf($language->get($langTextPrefix . 'greeting'), $storeName);
            $data['text_link'] = $language->get($langTextPrefix . 'link');
            $data['text_download'] = $language->get($langTextPrefix . 'download');
            $data['text_order_detail'] = $language->get($langTextPrefix . 'order_detail');
            $data['text_instruction'] = $language->get($langTextPrefix . 'instruction');
            $data['text_order_id'] = $language->get($langTextPrefix . 'order_id');
            $data['text_date_added'] = $language->get($langTextPrefix . 'date_added');
            $data['text_payment_method'] = $language->get($langTextPrefix . 'payment_method');
            $data['text_shipping_method'] = $language->get($langTextPrefix . 'shipping_method');
            $data['text_email'] = $language->get($langTextPrefix . 'email');
            $data['text_telephone'] = $language->get($langTextPrefix . 'telephone');
            $data['text_ip'] = $language->get($langTextPrefix . 'ip');
            $data['text_payment_address'] = $language->get($langTextPrefix . 'payment_address');
            $data['text_shipping_address'] = $language->get($langTextPrefix . 'shipping_address');
            $data['text_product'] = $language->get($langTextPrefix . 'product');
            $data['text_model'] = $language->get($langTextPrefix . 'model');
            $data['text_quantity'] = $language->get($langTextPrefix . 'quantity');
            $data['text_price'] = $language->get($langTextPrefix . 'price');
            $data['text_total'] = $language->get($langTextPrefix . 'total');
            $data['text_footer'] = $language->get($langTextPrefix . 'footer');

            if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
                $data['text_powered'] = $language->get($langTextPrefix . 'powered');
            }

            $data['logo'] = $config->get('config_url') . 'image/' . $config->get('config_logo');
            $data['store_name'] = $order_info['store_name'];
            $data['store_url'] = $order_info['store_url'];
            $data['customer_id'] = $order_info['customer_id'];
            $data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;

            if ($download_status) {
                $data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
            } else {
                $data['download'] = '';
            }

            $data['order_id'] = $order_id;
            $data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
            $data['payment_method'] = $order_info['payment_method'];
            $data['shipping_method'] = $order_info['shipping_method'];
            $data['email'] = $order_info['email'];
            $data['telephone'] = $order_info['telephone'];
            $data['ip'] = $order_info['ip'];

            if (version_compare($this->cartVars['dbVersion'], '2', '>=')) {
                $data['text_order_status'] = $language->get($langTextPrefix . 'order_status');
                $data['order_status'] = $order_status;
            }

            if ($commentFromAdmin) {
                $data['comment'] = nl2br($commentFromAdmin);
            } else {
                $data['comment'] = '';
            }

            if ($order_info['payment_address_format']) {
                $format = $order_info['payment_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['payment_firstname'],
                'lastname'  => $order_info['payment_lastname'],
                'company'   => $order_info['payment_company'],
                'address_1' => $order_info['payment_address_1'],
                'address_2' => $order_info['payment_address_2'],
                'city'      => $order_info['payment_city'],
                'postcode'  => $order_info['payment_postcode'],
                'zone'      => $order_info['payment_zone'],
                'zone_code' => $order_info['payment_zone_code'],
                'country'   => $order_info['payment_country']
            );

            $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

            if ($order_info['shipping_address_format']) {
                $format = $order_info['shipping_address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}'
            );

            $replace = array(
                'firstname' => $order_info['shipping_firstname'],
                'lastname'  => $order_info['shipping_lastname'],
                'company'   => $order_info['shipping_company'],
                'address_1' => $order_info['shipping_address_1'],
                'address_2' => $order_info['shipping_address_2'],
                'city'      => $order_info['shipping_city'],
                'postcode'  => $order_info['shipping_postcode'],
                'zone'      => $order_info['shipping_zone'],
                'zone_code' => $order_info['shipping_zone_code'],
                'country'   => $order_info['shipping_country']
            );

            $data['shipping_address'] = str_replace(
                array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format)))
            );

            if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
                $getValue = function($option) {
                    return utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                };
            } else {
                $loader->model('tool/upload');
                $getValue = function($option) use ($registry) {
                    /**
                     * @var ModelToolUpload $modelToolUpload
                     */
                    $modelToolUpload = $registry->get('model_tool_upload');
                    $upload_info = $modelToolUpload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        return $upload_info['name'];
                    } else {
                        return '';
                    }
                };
            }

            // Products
            $data['products'] = array();

            foreach ($order_product_query->rows as $product) {
                $option_data = array();

                $order_option_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

                foreach ($order_option_query->rows as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $value = $getValue($option);
                    }

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                $data['products'][] = array(
                    'name'     => $product['name'],
                    'model'    => $product['model'],
                    'option'   => $option_data,
                    'quantity' => $product['quantity'],
                    'price'    => $currency->format($product['price'] + ($config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                    'total'    => $currency->format($product['total'] + ($config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
                );
            }

            // Vouchers
            $data['vouchers'] = array();

            foreach ($order_voucher_query->rows as $voucher) {
                $data['vouchers'][] = array(
                    'description' => $voucher['description'],
                    'amount'      => $currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
                );
            }

            // Order Totals
            foreach ($order_total_query->rows as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text'  => $currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
                );
            }

            if (version_compare($this->cartVars['dbVersion'], '2', '<')) {
                $template = new Template();
                $template->data = $data;
                if (file_exists(DIR_TEMPLATE . $config->get('config_template') . '/template/mail/order.tpl')) {
                    $html = $template->fetch($config->get('config_template') . '/template/mail/order.tpl');
                } else {
                    $html = $template->fetch($themeDefaultDir . '/template/mail/order.tpl');
                }
            } elseif (version_compare($this->cartVars['dbVersion'], '2.2', '<')) {
                if (file_exists(DIR_TEMPLATE . $config->get('config_template') . '/template/mail/order.tpl')) {
                    $html = $loader->view($config->get('config_template') . '/template/mail/order.tpl', $data);
                } else {
                    $html = $loader->view($themeDefaultDir . '/template/mail/order.tpl', $data);
                }
            } elseif (version_compare($this->cartVars['dbVersion'], '3', '<')) {
                $html = $loader->view($themeDefaultDir . '/template/mail/order', $data);
            } else {
                $html = $loader->view($themeDefaultDir . '/template/mail/order_add', $data);
            }

            // Text Mail
            $text = sprintf($language->get($langTextPrefix . 'greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
            $text .= $language->get($langTextPrefix . 'order_id') . ' ' . $order_id . "\n";
            $text .= $language->get($langTextPrefix . 'date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
            $text .= $language->get($langTextPrefix . 'order_status') . ' ' . $order_status . "\n\n";

            if ($commentFromAdmin) {
                $text .= $language->get($langTextPrefix . 'instruction') . "\n\n";
                $text .= $commentFromAdmin . "\n\n";
            }

            // Products
            $text .= $language->get($langTextPrefix . 'products') . "\n";

            foreach ($order_product_query->rows as $product) {
                $text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($currency->format($product['total'] + ($config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

                $order_option_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");

                foreach ($order_option_query->rows as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $value = $getValue($option);
                    }

                    $text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value) . "\n";
                }
            }

            foreach ($order_voucher_query->rows as $voucher) {
                $text .= '1x ' . $voucher['description'] . ' ' . $currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
            }

            $text .= "\n";

            $text .= $language->get($langTextPrefix . 'order_total') . "\n";

            foreach ($order_total_query->rows as $total) {
                $text .= $total['title'] . ': ' . html_entity_decode($currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";
            }

            $text .= "\n";

            if ($order_info['customer_id']) {
                $text .= $language->get($langTextPrefix . 'link') . "\n";
                $text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
            }

            if ($download_status) {
                $text .= $language->get($langTextPrefix . 'download') . "\n";
                $text .= $order_info['store_url'] . 'index.php?route=account/download' . "\n\n";
            }

            // Comment
            if ($order_info['comment']) {
                $text .= $language->get($langTextPrefix . 'comment') . "\n\n";
                $text .= $order_info['comment'] . "\n\n";
            }

            $text .= $language->get($langTextPrefix . 'footer') . "\n\n";

            if ($notifyCustomer) {
                $mail = new Mail();
                $mail->protocol = $config->get('config_mail_protocol');
                $mail->parameter = $config->get('config_mail_parameter');
                $mail->setTo($order_info['email']);
                $mail->setFrom($config->get('config_email'));
                $mail->{$propertyPrefix . 'hostname'} = $config->get('config_' . $configPrefix . 'smtp_host');
                $mail->{$propertyPrefix . 'username'} = $config->get('config_' . $configPrefix . 'smtp_username');
                $mail->{$propertyPrefix . 'password'} = $config->get('config_' . $configPrefix . 'smtp_password');
                $mail->{$propertyPrefix . 'port'} = $config->get('config_' . $configPrefix . 'smtp_port');
                $mail->{$propertyPrefix . 'timeout'} = $config->get('config_' . $configPrefix . 'smtp_timeout');
                $mail->setSender($storeName);
                $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                $mail->setHtml($html);
                $mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
                $mail->send();
            }
        }

        if ($notifyAdmin) {
            $subject = sprintf($language->get($langTextPrefix . 'subject'), html_entity_decode($config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_id);

            if (version_compare($this->cartVars['dbVersion'], '2', '>=')) {
                // HTML Mail
                $data['text_greeting'] = $language->get($langTextPrefix . 'received');

                if ($commentFromAdmin) {
                    if ($order_info['comment']) {
                        $data['comment'] = nl2br($commentFromAdmin) . '<br/><br/>' . $order_info['comment'];
                    } else {
                        $data['comment'] = nl2br($commentFromAdmin);
                    }
                } else {
                    if ($order_info['comment']) {
                        $data['comment'] = $order_info['comment'];
                    } else {
                        $data['comment'] = '';
                    }
                }

                $data['text_download'] = '';

                $data['text_footer'] = '';

                $data['text_link'] = '';
                $data['link'] = '';
                $data['download'] = '';

                if (version_compare($this->cartVars['dbVersion'], '2.2', '<')) {
                    if (file_exists(DIR_TEMPLATE . $config->get('config_template') . '/template/mail/order.tpl')) {
                        $adminHtml = $loader->view($config->get('config_template') . '/template/mail/order.tpl', $data);
                    } else {
                        $adminHtml = $loader->view($themeDefaultDir . '/template/mail/order.tpl', $data);
                    }
                } elseif (version_compare($this->cartVars['dbVersion'], '3', '<')) {
                    $adminHtml = $loader->view($themeDefaultDir . '/template/mail/order', $data);
                } else {
                    $adminHtml = $loader->view($themeDefaultDir . '/template/mail/order_add', $data);
                }
            }

            // Text
            $text  = $language->get($langTextPrefix . 'received') . "\n\n";
            $text .= $language->get($langTextPrefix . 'order_id') . ' ' . $order_id . "\n";
            $text .= $language->get($langTextPrefix . 'date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
            $text .= $language->get($langTextPrefix . 'order_status') . ' ' . $order_status . "\n\n";
            $text .= $language->get($langTextPrefix . 'products') . "\n";

            foreach ($order_product_query->rows as $product) {
                $text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($currency->format($product['total'] + ($config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

                $order_option_query = $db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");

                foreach ($order_option_query->rows as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                    }

                    $text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value) . "\n";
                }
            }

            foreach ($order_voucher_query->rows as $voucher) {
                $text .= '1x ' . $voucher['description'] . ' ' . $currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
            }

            $text .= "\n";

            $text .= $language->get($langTextPrefix . 'order_total') . "\n";

            foreach ($order_total_query->rows as $total) {
                $text .= $total['title'] . ': ' . html_entity_decode($currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";
            }

            $text .= "\n";

            if ($order_info['comment']) {
                $text .= $language->get($langTextPrefix . 'comment') . "\n\n";
                $text .= $order_info['comment'] . "\n\n";
            }

            $mail = new Mail();
            $mail->protocol = $config->get('config_mail_protocol');
            $mail->parameter = $config->get('config_mail_parameter');
            $mail->{$propertyPrefix . 'hostname'} = $config->get('config_' . $configPrefix . 'smtp_host');
            $mail->{$propertyPrefix . 'username'}  = $config->get('config_' . $configPrefix . 'smtp_username');
            $mail->{$propertyPrefix . 'password'} = $config->get('config_' . $configPrefix . 'smtp_password');
            $mail->{$propertyPrefix . 'port'} = $config->get('config_' . $configPrefix . 'smtp_port');
            $mail->{$propertyPrefix . 'timeout'} = $config->get('config_' . $configPrefix . 'smtp_timeout');

            $mail->setTo($config->get('config_email'));
            $mail->setFrom($config->get('config_email'));
            $mail->setSender($storeName);
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            $mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
            if (isset($adminHtml)) {
                $mail->setHtml($adminHtml);
            }

            $mail->send();

            // Send to additional alert emails
            $emails = explode(',', $config->get('config_alert_emails'));

            foreach ($emails as $email) {
                if ($email && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

}



/**
 * Class M1_Config_Adapter_Mijoshop
 */
class M1_Config_Adapter_Mijoshop extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Mijoshop constructor.
     */
    public function __construct()
    {
        require_once M1_STORE_BASE_DIR . "/configuration.php";

        $mijoConf = M1_STORE_BASE_DIR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_mijoshop' . DIRECTORY_SEPARATOR . 'mijoshop.xml';

        if (file_exists($mijoConf)) {
            $xml = simplexml_load_file($mijoConf);
            $this->cartVars['dbVersion'] = (string)$xml->version[0];
        }

        if (class_exists("JConfig")) {

            $jconfig = new JConfig();

            $this->setHostPort($jconfig->host);
            $this->dbname   = $jconfig->db;
            $this->username = $jconfig->user;
            $this->password = $jconfig->password;

        } else {

            $this->setHostPort($host);
            $this->dbname   = $db;
            $this->username = $user;
            $this->password = $password;
        }

        $this->imagesDir              = "components/com_mijoshop/opencart/image/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }

}



/**
 * Class M1_Config_Adapter_Magento1212
 */
class M1_Config_Adapter_Magento1212 extends M1_Config_Adapter
{

    const ERROR_CODE_SUCCESS = 0;
    const ERROR_CODE_INTERNAL_ERROR = 2;

    private $_magentoVersionMajor = null;
    private $_countTry = 0;

    /**
     * M1_Config_Adapter_Magento1212 constructor.
     */
    public function __construct()
    {
        if (file_exists(M1_STORE_BASE_DIR . '../app/etc/env.php')) {
            define('M1_STORE_ROOT_DIR', realpath(M1_STORE_BASE_DIR . '..') . '/');
            $this->_magento2();
            $this->_magentoVersionMajor = 2;
        } else {
            define('M1_STORE_ROOT_DIR', M1_STORE_BASE_DIR);

            if (file_exists(M1_STORE_BASE_DIR . 'app/etc/env.php')) {
                $this->_magento2();
                $this->_magentoVersionMajor = 2;
            } else {
                $this->_magento1();
                $this->_magentoVersionMajor = 1;
            }
        }
    }

    /**
     * @param array $a2cData
     *
     * @return mixed
     * @throws Exception
     */
    public function getActiveModules(array $a2cData)
    {
        $response = array(
            'error_code' => self::ERROR_CODE_SUCCESS,
            'error'      => null,
            'result'     => array(),
        );

        $reportError = function (Throwable $e) use ($response) {
            $response['error'] = $e->getMessage();
            $response['error_code'] = self::ERROR_CODE_INTERNAL_ERROR;

            return $response;
        };

        if ($this->_magentoVersionMajor === 2) {
            try {
                require M1_STORE_ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

                $bootstrap = \Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                $objectManager = $bootstrap->getObjectManager();
                $state = $objectManager->get('Magento\Framework\App\State');
                $state->setAreaCode('global');

                /**
                 * @var Magento\Framework\Module\FullModuleList $fullModuleList
                 */
                $fullModuleList = $objectManager->get('\Magento\Framework\Module\FullModuleList');
                $modules = $fullModuleList->getAll();

                $response = array(
                    'error_code' => self::ERROR_CODE_SUCCESS,
                    'error'      => null,
                    'result'     => $modules,
                );
            } catch (Exception $e) {
                return $reportError($e);
            } catch (Throwable $e) {
                return $reportError($e);
            }
        } else {
            return $reportError(new Exception('Action is not supported'));
        }

        return $response;
    }

    /**
     * @param array $a2cData
     *
     * @return mixed
     * @throws Exception
     */
    public function productUpdateAction(array $a2cData)
    {
        if ($this->_magentoVersionMajor === 2) {
            do {
                try {
                    return $this->_productUpdateMage2($a2cData);
                } catch (Exception $e) {
                    if (preg_match('/deadlock/', $e->getMessage())) {
                        usleep(rand(1000000, 3000000));
                    } else {
                        throw $e;
                    }
                }
            } while (++ $this->_countTry < 3);

            if (isset($e)) {
                throw $e;
            }
        } else {
            throw new Exception('Action is not supported');
        }
    }

    /**
     * @param Magento\Framework\ObjectManagerInterface $objectManager
     * @param Magento\Catalog\Model\ProductRepository $productRepo
     * @param int $id
     * @param int $storeId
     * @param array $attributeCodes
     * @return Magento\Catalog\Model\Product
     *
     */
    private function _getProduct($objectManager, $productRepo, $id, $storeId, array $attributeCodes)
    {
        /**
         * @var Magento\Catalog\Model\Attribute\ScopeOverriddenValue $scopeOverriddenVal
         * @var Magento\Catalog\Model\ProductRepository $productRepo
         * @var Magento\Catalog\Model\Product $product
         * @var Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor $attribute
         * @var Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter $attributeFilter
         */

        $attributeFilter = $objectManager->get('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter');
        $scopeOverriddenVal = $objectManager->get('Magento\Catalog\Model\Attribute\ScopeOverriddenValue');
        $product = $productRepo->getById($id, true, (string)$storeId, true);
        $attributes = $product->getAttributes();
        $useDefaults = array();

        $newData['current_store_id'] = (string)$storeId;
        $newData['current_product_id'] = (string)$id;

        foreach ($attributes as $attributeCode => $attribute) {
            if ($attribute->isStatic() || $attribute->isScopeGlobal() || in_array($attributeCode, $attributeCodes)) {
                continue;
            }

            $useDefaults[$attributeCode] = $scopeOverriddenVal->containsValue(
                'Magento\Catalog\Api\Data\ProductInterface',
                $product,
                $attributeCode,
                (string)$storeId
            ) ? '0' : '1';
        }

        $productData = $attributeFilter->prepareProductAttributes($product, $product->getData(), $useDefaults);
        $product->addData($productData);

        return $product;
    }

    /**
     * @param mixed $current Current value
     * @param mixed $new     New Value
     *
     * @return bool
     */
    private function _isValuesEqual($current, $new)
    {
        switch (gettype($new)) {
            case 'integer': $current = (int)$current; break;
            case 'double': $current = (float)$current; break;
            case 'float': $current = (float)$current; break;
            case 'string': $current = (string)$current; break;
            case 'boolean': $current = (string)$current; break;
        };

        return $current === $new;
    }

    /**
     * @param array $data Data
     *
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function _productUpdateMage2(array $data)
    {
        require M1_STORE_ROOT_DIR . DIRECTORY_SEPARATOR  . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

        $bootstrap = \Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
        $objectManager = $bootstrap->getObjectManager();
        $state = $objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('global');

        /**
         * @var Magento\Store\Model\StoreManager $storeManager
         * @var Magento\Catalog\Model\Product $product
         * @var Magento\Catalog\Model\ProductRepository $productRepo
         * @var Magento\Framework\App\Request\DataPersistor $dataPersistor
         * @var Magento\Framework\App\Config $config
         */
        $productRepo = $objectManager->get('Magento\Catalog\Model\ProductRepository');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManager');
        $store = $storeManager->getStore((string)$data['store_id']);
        $storeManager->setCurrentStore($store->getCode());
        $config = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $isPricesPerWebsite = (int)$config->getValue('catalog/price/scope', Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) === 1;

        $isUpdated = false;
        $isProductSaved = false;

        if (empty($data['attributes'])) {
            $product = $productRepo->getById($data['entity_id'], false, 0, true);
        } else {
            foreach ($data['attributes'] as $storeId => $values) {
                $store = $storeManager->getStore($storeId);
                $storeManager->setCurrentStore($store->getCode());
                if ($storeId === 0) {
                    $product = $productRepo->getById($data['entity_id'], true, 0, true);
                } else {
                    $product = $this->_getProduct($objectManager, $productRepo, $data['entity_id'], $storeId, array_keys($values));
                }

                $storedData = $product->getStoredData();
                $productData = $product->getStoredData();

                $newData = array();
                foreach ($values as $attrCode => $value) {
                    if ($attrCode === 'url_key') {
                        $value = $product->getUrlModel()->formatUrlKey($value);
                    }

                    $attributes = $product->getAttributes();
                    $hasAttribute = isset($attributes[$attrCode]);
                    $currentValue = isset($productData[$attrCode]) ? $productData[$attrCode] : (isset($storedData[$attrCode]) ? $storedData[$attrCode] : null);

                    if ($hasAttribute && !$this->_isValuesEqual($currentValue, $value)) {
                        if ($attrCode === 'url_key' && $data['save_rewrites_history']) {
                            $newData['url_key_create_redirect'] = $product->getUrlKey();
                            $newData['save_rewrites_history']   = true;
                        }

                        if ($storeId > 0
                            && $isPricesPerWebsite
                            && $attributes[$attrCode]->getBackendModel() === 'Magento\Catalog\Model\Product\Attribute\Backend\Price'
                        ) {
                            $this->setPricePerStore($objectManager, $data['entity_id'], $storeId, $attrCode, $value);
                            $isUpdated = true;
                        } else {
                            $newData[$attrCode] = $value;
                        }
                    }
                }

                if (!empty($newData)) {
                    $product->addData($newData);
                    $product = $productRepo->save($product);
                    $dataPersistor = $objectManager->get('\Magento\Framework\App\Request\DataPersistor');
                    $dataPersistor->clear('catalog_product');

                    $isUpdated = true;
                    $isProductSaved = true;
                }
            }
        }

        if (!empty($data['inventory'])) {
            /**
             * @var Magento\CatalogInventory\Model\Configuration $stockConf
             */
            $stockConf = $objectManager->get('Magento\CatalogInventory\Model\Configuration');
            $isQtySupportedArr = $stockConf->getIsQtyTypeIds();
            $isQtySupported = isset($isQtySupportedArr[$product->getTypeId()]) && $isQtySupportedArr[$product->getTypeId()];

            /**
             * @var Magento\Framework\Module\Manager $moduleManager
             */
            $moduleManager = $objectManager->get('Magento\Framework\Module\Manager');
            $msiEnabled = $moduleManager->isEnabled('Magento_Inventory');

            if (isset($data['inventory']['stockItem']) || isset($data['inventory']['a2c'])) {
                /**
                 * @var Magento\CatalogInventory\Model\StockRegistry $stockRegistry
                 */
                $stockRegistry = $objectManager->get('Magento\CatalogInventory\Model\StockRegistry');
                /**
                 * @var Magento\CatalogInventory\Model\Stock\Item $stockItem
                 */
                $stockItem = $stockRegistry->getStockItem($data['entity_id'], $store->getWebsiteId());

                if (isset($data['inventory']['stockItem'])) {
                    foreach ($data['inventory']['stockItem'] as $attrCode => $value) {
                        if ($attrCode === 'qty' && !$isQtySupported
                            || $this->_isValuesEqual($stockItem->getData($attrCode), $value)
                        ) {
                            continue;
                        }

                        $stockItem->setData($attrCode, $value);
                    }
                }

                if (isset($data['inventory']['a2c']['modify_qty']) && !$msiEnabled && $isQtySupported) {
                    $stockItem->setQty($stockItem->getQty() + $data['inventory']['a2c']['modify_qty']);
                }

                if ($stockItem->hasDataChanges()) {
                    $stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
                    $isUpdated = true;
                }
            }

            if ($msiEnabled && $isQtySupported && $this->_msiUpdateStock($data, $product, $objectManager, $store)) {
                /**
                 * @var Magento\CatalogInventory\Model\StockRegistryStorage $storage
                 */
                $storage = $objectManager->get('Magento\CatalogInventory\Model\StockRegistryStorage');
                $storage->clean();

                $isUpdated = true;
            }
        }

        if ($isUpdated) {
            if (!$isProductSaved) {
                $storeManager->setCurrentStore('admin');
                $product = $productRepo->getById($data['entity_id'], true, 0, true);
                $productRepo->save($product);
            }

            $product->cleanCache();

            /**
             * @var Magento\Framework\Event\ManagerInterface $eventManager
             */
            $eventManager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
            $eventManager->dispatch('clean_cache_by_tags', array('object' => $product));
        }

        return (int)$isUpdated;
    }

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param int $productId
     * @param int $storeId
     * @param string $code
     * @param float $value
     *
     * @return bool
     */
    protected function setPricePerStore($objectManager, $productId, $storeId, $code, $value)
    {
        /**
         * @var Magento\Catalog\Model\ProductFactory $factory
         * @var Magento\Catalog\Model\ResourceModel\Product $resource
         */
        $factory = $objectManager->get('Magento\Catalog\Model\ProductFactory');
        $resource = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $product = $factory->create();

        $resource->load($product, $productId);
        $product->setStoreId($storeId);
        $product->setData($code, $value);
        $resource->saveAttribute($product, $code);
    }

    /**
     * @param array $data
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\Store\Interceptor $store
     */
    private function _msiUpdateStock(array $data, $product, $objectManager, $store)
    {
        $isInventoryUpdated = false;

        if (isset($data['inventory']['sourceItem'])) {
            /**
             * @var \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
             */
            $searchCriteriaBuilderFactory = $objectManager->get('\Magento\Framework\Api\SearchCriteriaBuilderFactory');
            $searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter(
                \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU,
                $product->getSku()
            );

            $searchCriteriaBuilder->addFilter(
                \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE,
                $data['inventory']['sourceItem']['source_code']
            );

            /**
             * @var \Magento\Inventory\Model\SourceItemRepository $sourceItemRepo
             */
            $sourceItemRepo = $objectManager->get('\Magento\Inventory\Model\SourceItemRepository');
            $sourceItems = $sourceItemRepo->getList($searchCriteriaBuilder->create())->getItems();

            if (!empty($sourceItems)) {
                /**
                 * @var \Magento\Inventory\Model\SourceItem $sourceItem
                 */
                foreach ($sourceItems as $sourceItem) {
                    $isSourceItemUpdated = false;

                    foreach ($data['inventory']['sourceItem'] as $attrCode => $value) {
                        if (!$this->_isValuesEqual($sourceItem->getDataByKey($attrCode), $value)) {
                            $sourceItem->setData($attrCode, $value);
                            $isSourceItemUpdated = true;
                        }
                    }

                    if (isset($data['inventory']['a2c']['modify_qty'])) {
                        $sourceItem->setData(
                            'quantity',
                            number_format($sourceItem->getQuantity() + $data['inventory']['a2c']['modify_qty'], 4, '.', '')
                        );

                        $isSourceItemUpdated = true;
                    }

                    if ($isSourceItemUpdated) {
                        /**
                         * @var \Magento\Inventory\Model\SourceItem\Command\SourceItemsSave $sourceItemSave
                         */
                        $sourceItemSave = $objectManager->get('\Magento\Inventory\Model\SourceItem\Command\SourceItemsSave');
                        $sourceItemSave->execute($sourceItems);

                        $isInventoryUpdated = true;
                    }
                }

                if (isset($data['inventory']['a2c']['reserve_qty'])) {
                    /**
                     * @var \Magento\InventorySales\Model\StockResolver $stockResolver
                     */
                    $stockResolver = $objectManager->get('\Magento\InventorySales\Model\StockResolver');
                    $stockId = (int)$stockResolver->execute(
                        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                        $store->getWebsite()->getCode()
                    )->getStockId();

                    /**
                     * @var \Magento\InventoryReservations\Model\ReservationBuilder $reservationBuilder
                     */
                    $reservationBuilder = $objectManager->get('\Magento\InventoryReservations\Model\ReservationBuilder');
                    $reservationBuilder->setQuantity($data['inventory']['a2c']['reserve_qty']);
                    $reservationBuilder->setSku($product->getSku());
                    $reservationBuilder->setStockId($stockId);

                    $reservation = $reservationBuilder->build();

                    /**
                     * @var \Magento\InventoryReservations\Model\AppendReservations $appendReservation
                     */
                    $appendReservation = $objectManager->get('\Magento\InventoryReservations\Model\AppendReservations');
                    $appendReservation->execute(array($reservation));

                    $isInventoryUpdated = true;
                }
            }
        }

        return $isInventoryUpdated;
    }

    /**
     * @return void
     */
    protected function _magento1()
    {
        /**
         * @var SimpleXMLElement
         */
        $config = simplexml_load_file(M1_STORE_ROOT_DIR . 'app/etc/local.xml');
        $statuses = simplexml_load_file(M1_STORE_ROOT_DIR . 'app/code/core/Mage/Sales/etc/config.xml');

        $version = $statuses->modules->Mage_Sales->version;
        $result  = array();
        if (version_compare($version, '1.4.0.25') < 0) {
            $statuses = $statuses->global->sales->order->statuses;
            foreach ($statuses->children() as $status) {
                $result[$status->getName()] = (string)$status->label;
            }
        }

        if (file_exists(M1_STORE_ROOT_DIR . "app/Mage.php")) {
            $ver = file_get_contents(M1_STORE_ROOT_DIR . "app/Mage.php");
            if (preg_match("/getVersionInfo[^}]+\'major\' *=> *\'(\d+)\'[^}]+\'minor\' *=> *\'(\d+)\'[^}]+\'revision\' *=> *\'(\d+)\'[^}]+\'patch\' *=> *\'(\d+)\'[^}]+}/s", $ver, $match) == 1 ) {
                $mageVersion = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];
                $this->cartVars['dbVersion'] = $mageVersion;
                unset($match);
            }
        }

        $this->cartVars['orderStatus'] = $result;
        $this->cartVars['AdminUrl']    = (string)$config->admin->routers->adminhtml->args->frontName;
        $this->cartVars['dbPrefix']    = (string)$config->global->resources->db->table_prefix;

        $this->setHostPort((string)$config->global->resources->default_setup->connection->host);
        $this->username  = (string)$config->global->resources->default_setup->connection->username;
        $this->dbname    = (string)$config->global->resources->default_setup->connection->dbname;
        $this->password  = (string)$config->global->resources->default_setup->connection->password;
        $this->tblPrefix = (string)$config->global->resources->db->table_prefix;

        $this->imagesDir              = 'media/';
        $this->categoriesImagesDir    = $this->imagesDir . "catalog/category/";
        $this->productsImagesDir      = $this->imagesDir . "catalog/product/";
        $this->manufacturersImagesDir = $this->imagesDir;
        @unlink(M1_STORE_ROOT_DIR . 'app/etc/use_cache.ser');
    }

    /**
     * @return void
     */
    protected function _magento2()
    {
        /**
         * @var array
         */
        $config = @include(M1_STORE_ROOT_DIR . 'app/etc/env.php');

        $db = array();
        foreach ($config['db']['connection'] as $connection) {
            if ($connection['active'] == 1) {
                $db = $connection;
                break;
            }
        }

        if (empty($db)) {
            $db = isset($config['db']['connection']['default'])
                ? $config['db']['connection']['default']
                : die('[ERROR] MySQL Query Error: Can not connect to DB');
        }

        $this->setHostPort((string)$db['host']);
        $this->username  = (string)$db['username'];
        $this->dbname    = (string)$db['dbname'];
        $this->password  = (string)$db['password'];
        $this->tblPrefix = (string)$config['db']['table_prefix'];

        $version = '';
        if (file_exists(M1_STORE_ROOT_DIR . 'composer.json')) {
            $string = file_get_contents(M1_STORE_ROOT_DIR . 'composer.json');
            $json = json_decode($string, true);

            if (isset($json['require']['magento/product-enterprise-edition'])) {
                $version = 'EE.' . $json['require']['magento/product-enterprise-edition'];
            } elseif (isset($json['require']['magento/magento-cloud-metapackage'])) {
                $version = 'EE.' . $json['require']['magento/magento-cloud-metapackage'];
            }
        }

        if (!$version || preg_match('/[\^*\-<>=~|]/', $version)) {
            try {
                require M1_STORE_ROOT_DIR . 'app/bootstrap.php';

                $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                $objectManager = $bootstrap->getObjectManager();
                $magentoVersion = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
                $edition = ($magentoVersion->getEdition() === 'Enterprise' ? 'EE.' : '');
                $version = $edition . $magentoVersion->getVersion();
            } catch(Exception $e) {
                die('ERROR_READING_STORE_CONFIG_FILE');
            }
        }

        if (!$version && file_exists(M1_STORE_ROOT_DIR . 'vendor/magento/framework/AppInterface.php')) {
            @include M1_STORE_ROOT_DIR . 'vendor/magento/framework/AppInterface.php';

            if (defined('\Magento\Framework\AppInterface::VERSION')) {
                $version = \Magento\Framework\AppInterface::VERSION;
            } else {
                $version = '2.0.0';
            }
        }

        $this->cartVars['dbVersion'] = $version;

        if (isset($db['initStatements']) && $db['initStatements'] != '') {
            $this->cartVars['dbCharSet'] = $db['initStatements'];
        }

        $this->imagesDir              = 'pub/media/';
        $this->categoriesImagesDir    = $this->imagesDir . 'catalog/category/';
        $this->productsImagesDir      = $this->imagesDir . 'catalog/product/';
        $this->manufacturersImagesDir = $this->imagesDir;
    }
}

/**
 * Class M1_Config_Adapter_JooCart
 */
class M1_Config_Adapter_JooCart extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_JooCart constructor.
     */
    public function __construct()
    {
        require_once M1_STORE_BASE_DIR . "/configuration.php";

        if (class_exists("JConfig")) {

            $jconfig = new JConfig();

            $this->setHostPort($jconfig->host);
            $this->dbname   = $jconfig->db;
            $this->username = $jconfig->user;
            $this->password = $jconfig->password;

        } else {

            $this->setHostPort($mosConfig_host);
            $this->dbname   = $mosConfig_db;
            $this->username = $mosConfig_user;
            $this->password = $mosConfig_password;
        }

        $this->imagesDir              = "components/com_opencart/image/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }

}


/**
 * Class M1_Config_Adapter_Interspire
 */
class M1_Config_Adapter_Interspire extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Interspire constructor.
     */
    public function __construct()
    {
        require_once M1_STORE_BASE_DIR . "config/config.php";

        $this->setHostPort($GLOBALS['ISC_CFG']["dbServer"]);
        $this->username = $GLOBALS['ISC_CFG']["dbUser"];
        $this->password = $GLOBALS['ISC_CFG']["dbPass"];
        $this->dbname   = $GLOBALS['ISC_CFG']["dbDatabase"];

        $this->imagesDir = $GLOBALS['ISC_CFG']["ImageDirectory"];
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        define('DEFAULT_LANGUAGE_ISO2',$GLOBALS['ISC_CFG']["Language"]);

        $version = $this->getCartVersionFromDb("database_version", $GLOBALS['ISC_CFG']["tablePrefix"] . "config", '1');
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }
    }
}

/**
 * Class M1_Config_Adapter_Hhgmultistore
 */
class M1_Config_Adapter_Hhgmultistore extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Hhgmultistore constructor.
     */
    public function __construct()
    {
        define('SITE_PATH', '');
        define('WEB_PATH', '');
        require_once M1_STORE_BASE_DIR . "core/config/configure.php";
        require_once M1_STORE_BASE_DIR . "core/config/paths.php";

        $baseDir = "/store_files/1/";
        $this->imagesDir = $baseDir . DIR_WS_IMAGES;

        $this->categoriesImagesDir    = $baseDir . DIR_WS_CATEGORIE_IMAGES;
        $this->productsImagesDirs['info']  = $baseDir . DIR_WS_PRODUCT_INFO_IMAGES;
        $this->productsImagesDirs['org']   = $baseDir . DIR_WS_PRODUCT_ORG_IMAGES;
        $this->productsImagesDirs['thumb'] = $baseDir . DIR_WS_PRODUCT_THUMBNAIL_IMAGES;
        $this->productsImagesDirs['popup'] = $baseDir . DIR_WS_PRODUCT_POPUP_IMAGES;

        $this->manufacturersImagesDirs['img'] = $baseDir . DIR_WS_MANUFACTURERS_IMAGES;
        $this->manufacturersImagesDirs['org'] = $baseDir . DIR_WS_MANUFACTURERS_ORG_IMAGES;

        $this->host     = DB_SERVER;
        $this->username = DB_SERVER_USERNAME;
        $this->password = DB_SERVER_PASSWORD;
        $this->dbname   = DB_DATABASE;

        if (file_exists(M1_STORE_BASE_DIR . "/core/config/conf.hhg_startup.php")) {
            $ver = file_get_contents(M1_STORE_BASE_DIR . "/core/config/conf.hhg_startup.php");
            if (preg_match('/PROJECT_VERSION.+\((.+)\)\'\)/', $ver, $match) != 0) {
                $this->cartVars['dbVersion'] = $match[1];
                unset($match);
            }
        }
    }

}


/**
 * Class M1_Config_Adapter_Gambio
 */
class M1_Config_Adapter_Gambio extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Gambio constructor.
     */
    public function __construct()
    {
        $curDir = getcwd();

        chdir(M1_STORE_BASE_DIR);

        @require_once M1_STORE_BASE_DIR . "includes/configure.php";

        chdir($curDir);

        $this->imagesDir = DIR_WS_IMAGES;

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        if (defined('DIR_WS_PRODUCT_IMAGES')) {
            $this->productsImagesDir = DIR_WS_PRODUCT_IMAGES;
        }
        if (defined('DIR_WS_ORIGINAL_IMAGES')) {
            $this->productsImagesDir = DIR_WS_ORIGINAL_IMAGES;
        }
        $this->manufacturersImagesDir = $this->imagesDir;

        $this->host      = DB_SERVER;
        //$this->setHostPort(DB_SERVER);
        $this->username  = DB_SERVER_USERNAME;
        $this->password  = DB_SERVER_PASSWORD;
        $this->dbname    = DB_DATABASE;

        chdir(M1_STORE_BASE_DIR);
        if (file_exists(M1_STORE_BASE_DIR  . "includes" . DIRECTORY_SEPARATOR . 'application_top.php')) {
            $conf = file_get_contents (M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . "application_top.php");
            preg_match("/define\('PROJECT_VERSION.*/", $conf, $match);
            if (isset($match[0]) && !empty($match[0])) {
                preg_match("/\d.*/", $match[0], $project);
                if (isset($project[0]) && !empty($project[0])) {
                    $version = $project[0];
                    $version = str_replace(array(" ","-","_","'",");"), "", $version);
                    if ($version != '') {
                        $this->cartVars['dbVersion'] = strtolower($version);
                    }
                }
            } else {
                //if another oscommerce based cart
                if (file_exists(M1_STORE_BASE_DIR . DIRECTORY_SEPARATOR . 'version_info.php')) {
                    @require_once M1_STORE_BASE_DIR . DIRECTORY_SEPARATOR . "version_info.php";
                    if (defined('PROJECT_VERSION') && PROJECT_VERSION != '' ) {
                        $version = PROJECT_VERSION;
                        preg_match("/\d.*/", $version, $vers);
                        if (isset($vers[0]) && !empty($vers[0])) {
                            $version = $vers[0];
                            $version = str_replace(array(" ","-","_"), "", $version);
                            if ($version != '') {
                                $this->cartVars['dbVersion'] = strtolower($version);
                            }
                        }
                        //if zen_cart
                    } else {
                        if (defined('PROJECT_VERSION_MAJOR') && PROJECT_VERSION_MAJOR != '' ) {
                            $this->cartVars['dbVersion'] = PROJECT_VERSION_MAJOR;
                        }
                        if (defined('PROJECT_VERSION_MINOR') && PROJECT_VERSION_MINOR != '' ) {
                            $this->cartVars['dbVersion'] .= '.' . PROJECT_VERSION_MINOR;
                        }
                    }
                }
            }
        }

        if (file_exists(M1_STORE_BASE_DIR  . DIRECTORY_SEPARATOR . 'release_info.php')) {
            @include_once M1_STORE_BASE_DIR . DIRECTORY_SEPARATOR . "release_info.php";
            $this->cartVars['dbVersion'] = mb_substr($gx_version, 1);
        }
        chdir($curDir);
    }

}



/**
 * Class M1_Config_Adapter_Cubecart3
 */
class M1_Config_Adapter_Cubecart3 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Cubecart3 constructor.
     */
    public function __construct()
    {
        include_once(M1_STORE_BASE_DIR . 'includes/global.inc.php');

        $this->setHostPort($glob['dbhost']);
        $this->dbname    = $glob['dbdatabase'];
        $this->username  = $glob['dbusername'];
        $this->password  = $glob['dbpassword'];
        $this->tblPrefix = $glob['dbprefix'];

        $this->imagesDir = 'images/uploads';
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }
}

/**
 * Class M1_Config_Adapter_Cubecart
 */
class M1_Config_Adapter_Cubecart extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Cubecart constructor.
     */
    public function __construct()
    {
        include_once(M1_STORE_BASE_DIR . 'includes/global.inc.php');

        $this->setHostPort($glob['dbhost']);
        $this->dbname    = $glob['dbdatabase'];
        $this->username  = $glob['dbusername'];
        $this->password  = $glob['dbpassword'];
        $this->tblPrefix = $glob['dbprefix'];

        $this->imagesDir = 'images';
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
        $dirHandle = opendir(M1_STORE_BASE_DIR . 'language/');
        //settings for cube 5
        $languages = array();
        while ($dirEntry = readdir($dirHandle)) {
            $info = pathinfo($dirEntry);
            $xmlflag = false;

            if (isset($info['extension'])) {
                $xmlflag = strtoupper($info['extension']) != "XML" ? true : false;
            }
            if (is_dir(M1_STORE_BASE_DIR . 'language/' . $dirEntry) || $dirEntry == '.' || $dirEntry == '..' || strpos($dirEntry, "_") !== false || $xmlflag) {
                continue;
            }
            $configXml = simplexml_load_file(M1_STORE_BASE_DIR . 'language/'.$dirEntry);
            if ($configXml->info->title) {
                $lang['name'] = (string)$configXml->info->title;
                $lang['code'] = substr((string)$configXml->info->code, 0, 2);
                $lang['locale'] = substr((string)$configXml->info->code, 0, 2);
                $lang['currency'] = (string)$configXml->info->default_currency;
                $lang['fileName'] = str_replace(".xml", "", $dirEntry);
                $languages[] = $lang;
            }
        }
        if (!empty($languages)) {
            $this->cartVars['languages'] = $languages;
        }

        $conf = false;
        if (file_exists(M1_STORE_BASE_DIR  . 'ini.inc.php')) {
            $conf = file_get_contents (M1_STORE_BASE_DIR . 'ini.inc.php');
        } elseif (file_exists(M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . 'ini.inc.php')) {
            $conf = file_get_contents (M1_STORE_BASE_DIR . "includes" . DIRECTORY_SEPARATOR . 'ini.inc.php');
        }

        if ($conf !== false) {
            preg_match('/\$ini\[[\'"]ver[\'"]\]\s*=\s*[\'"](.*?)[\'"]\s*;/', $conf, $match);
            if (isset($match[1]) && !empty($match[1])) {
                $this->cartVars['dbVersion'] = strtolower($match[1]);
            } else {
                preg_match("/define\(['\"]CC_VERSION['\"]\s*,\s*['\"](.*?)['\"]\)/", $conf, $match);
                if (isset($match[1]) && !empty($match[1])) {
                    $this->cartVars['dbVersion'] = strtolower($match[1]);
                }
            }
        }
    }
}

/**
 * Class M1_Config_Adapter_Cscart203
 */
class M1_Config_Adapter_Cscart203 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Cscart203 constructor.
     */
    public function __construct()
    {
        define("IN_CSCART", 1);
        define("CSCART_DIR", M1_STORE_BASE_DIR);
        define("AREA", 1);
        define("DIR_ROOT", M1_STORE_BASE_DIR);
        define("DIR_CSCART", M1_STORE_BASE_DIR);
        define('DS', DIRECTORY_SEPARATOR);
        define('BOOTSTRAP', '');
        require_once M1_STORE_BASE_DIR . 'config.php';
        defined('DIR_IMAGES') or define('DIR_IMAGES', DIR_ROOT . '/images/');

        //For CS CART 1.3.x
        if (isset($db_host) && isset($db_name) && isset($db_user) && isset($db_password)) {
            $this->setHostPort($db_host);
            $this->dbname = $db_name;
            $this->username = $db_user;
            $this->password = $db_password;
            $this->imagesDir = str_replace(M1_STORE_BASE_DIR, '', IMAGES_STORAGE_DIR);
            $this->tblPrefix = 'cscart_';
        } else {

            $this->setHostPort($config['db_host']);
            $this->dbname = $config['db_name'];
            $this->username = $config['db_user'];
            $this->password = $config['db_password'];
            $this->imagesDir = str_replace(M1_STORE_BASE_DIR, '', DIR_IMAGES);

            if (isset($config['table_prefix'])) {
                $this->tblPrefix = $config['table_prefix'];
            } elseif (defined('TABLE_PREFIX')) {
                $this->tblPrefix = TABLE_PREFIX;
            } else {
                $this->tblPrefix = '';
            }
        }

        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        if (defined('MAX_FILES_IN_DIR')) {
            $this->cartVars['cs_max_files_in_dir'] = MAX_FILES_IN_DIR;
        }

        if (defined('PRODUCT_VERSION')) {
            $this->cartVars['dbVersion'] = PRODUCT_VERSION;
        }
    }

}


/**
 * Class M1_Config_Adapter_AceShop
 */
class M1_Config_Adapter_AceShop extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_AceShop constructor.
     */
    public function __construct()
    {
        require_once M1_STORE_BASE_DIR . "/configuration.php";

        if (class_exists("JConfig")) {

            $jconfig = new JConfig();

            $this->setHostPort($jconfig->host);
            $this->dbname   = $jconfig->db;
            $this->username = $jconfig->user;
            $this->password = $jconfig->password;

        } else {

            $this->setHostPort($mosConfig_host);
            $this->dbname   = $mosConfig_db;
            $this->username = $mosConfig_user;
            $this->password = $mosConfig_password;
        }

        $this->imagesDir = "components/com_aceshop/opencart/image/";
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;
    }

}


/**
 * Class M1_Config_Adapter_Pinnacle361
 */
class M1_Config_Adapter_Pinnacle361 extends M1_Config_Adapter
{

    /**
     * M1_Config_Adapter_Pinnacle361 constructor.
     */
    public function __construct()
    {
        include_once M1_STORE_BASE_DIR . 'content/engine/engine_config.php';

        $this->imagesDir = 'images/';
        $this->categoriesImagesDir    = $this->imagesDir;
        $this->productsImagesDir      = $this->imagesDir;
        $this->manufacturersImagesDir = $this->imagesDir;

        //$this->Host = DB_HOST;
        $this->setHostPort(DB_HOST);
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;
        $this->tblPrefix = defined('DB_PREFIX') ? DB_PREFIX : '';

        $version = $this->getCartVersionFromDb(
            "value",
            "settings",
            "name = 'AppVer'"
        );
        if ($version != '') {
            $this->cartVars['dbVersion'] = $version;
        }
    }

}


/**
 * Class M1_Bridge_Action_Update
 */
class M1_Bridge_Action_Update
{
    private $_pathToTmpDir;

    /**
     * M1_Bridge_Action_Update constructor.
     */
    public function __construct()
    {
        $this->_pathToTmpDir = __DIR__ . DIRECTORY_SEPARATOR . "temp_a2c";
    }

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $response = new stdClass();
        $currentBridgeContent = file_get_contents(__FILE__);

        if (!($this->_checkBridgeDirPermission()
            && $this->_checkBridgeFilePermission())
        ) {
            $response->code    = 1;
            $response->message = "Bridge Update couldn't be performed. " .
                "Please change permission for bridge folder to 755, bridge file inside it to 644 and set appropriate owner";
            die(json_encode($response));
        }

        if (($data = $this->_downloadFile()) === false) {
            $response->code = 1;
            $response->message = "Can not download new bridge.";

            die(json_encode($response));
        }

        if ($data->body == '') {
            $response->code = 1;
            $response->message = 'New bridge is empty. Please contact us.';

            die(json_encode($response));
        } elseif (strpos($data->body, '<?php') !== 0) {
            $response->code = 1;
            $response->message = 'Downloaded file is not valid.';

            die(json_encode($response));
        }

        if (!$this->_writeToFile($data)) {
            $response->code = 1;
            $response->message = "Bridge is not updated! Please contact us.";

            if (!$this->_isBridgeOk()) {
                file_put_contents(__FILE__, $currentBridgeContent);
            }

            die(json_encode($response));
        }

        $response->code    = 0;
        $response->message = "Bridge successfully updated to latest version";

        if (!$this->_isBridgeOk()) {
            $response->code = 1;
            $response->message = "Bridge is not updated! Please contact us.";

            file_put_contents(__FILE__, $currentBridgeContent);
        }

        die(json_encode($response));
    }

    /**
     * @param string $uri
     * @param bool   $ignoreSsl
     *
     * @return stdClass
     */
    private function _fetch($uri, $ignoreSsl = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($ignoreSsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $response = new stdClass();

        $response->body          = curl_exec($ch);
        $response->httpCode      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response->contentType   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $response->contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);

        return $response;
    }

    /**
     * @return bool
     */
    public function _createTempDir()
    {
        @mkdir($this->_pathToTmpDir, 0755);

        return file_exists($this->_pathToTmpDir);
    }

    /**
     * @return bool
     */
    public function _removeTempDir()
    {
        @unlink($this->_pathToTmpDir . DIRECTORY_SEPARATOR . "bridge.php_a2c");
        @rmdir($this->_pathToTmpDir);
        return !file_exists($this->_pathToTmpDir);
    }

    /**
     * @return bool|stdClass
     */
    private function _downloadFile()
    {
        $file = $this->_fetch(M1_BRIDGE_DOWNLOAD_LINK);
        if ($file->httpCode == 200) {
            return $file;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function _checkBridgeDirPermission()
    {
        return is_writeable(dirname(__FILE__));
    }

    /**
     * @return bool
     */
    protected function _checkBridgeFilePermission()
    {
        $pathToFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bridge.php';

        return is_writeable($pathToFile);
    }

    private function _isBridgeOk()
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(__FILE__, true);
        }

        $checkBridge = $this->_fetch(
            $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'],
            true
        );

        return strpos($checkBridge->body, 'BRIDGE INSTALLED.') !== false;
    }

    /**
     * @param $data
     * @return bool
     */
    private function _writeToFile($data)
    {
        if (function_exists("file_put_contents")) {
            $bytes = file_put_contents(__FILE__, $data->body);
            return $bytes == $data->contentLength;
        }

        $handle = @fopen(__FILE__, 'w+');
        $bytes = fwrite($handle, $data->body);
        @fclose($handle);

        return $bytes == $data->contentLength;
    }

}

/**
 * Class M1_Bridge_Action_SetProductStores
 */
class M1_Bridge_Action_SetProductStores
{
    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => false);
        switch ($bridge->config->cartType) {
            case 'Magento1212':
                $productId = $_POST['product_id'];
                $websiteIds = explode(',', $_POST['store_ids']);

                try {
                    $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                    if (version_compare($version, '2.0.0', '<')) {
                        require M1_STORE_ROOT_DIR . '/app/Mage.php';
                        Mage::app();
                        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); //required

                        $product = Mage::getModel('catalog/product')->load($productId);
                        $product->setWebsiteIds($websiteIds);
                        $product->save();
                        $response['data'] = true;
                    } else {
                        require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $objectManager = $bootstrap->getObjectManager();

                        $state = $objectManager->get('\Magento\Framework\App\State');
                        $state->setAreaCode('frontend'); //required

                        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                        $product->setWebsiteIds($websiteIds);
                        $product->save();
                        $response['data'] = true;
                    }

                } catch (Exception $e) {
                    $response['error']['message'] = $e->getMessage();
                    $response['error']['code'] = $e->getCode();
                }

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }
}

/**
 * Class M1_Bridge_Action_Send_Notification
 */
class M1_Bridge_Action_Send_Notification
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $response = array(
            'error' => false,
            'code' => null,
            'message' => null,
        );

        try {
            switch ($_POST['cartId']) {
                case 'Magento1212' :
                    if (!file_exists(M1_STORE_ROOT_DIR . '/app/etc/env.php')) {
                        include_once M1_STORE_ROOT_DIR . 'includes/config.php';
                        include_once M1_STORE_ROOT_DIR . 'app/bootstrap.php';
                        include_once M1_STORE_ROOT_DIR . 'app/Mage.php';
                        Mage::init();

                    } else {
                        include_once M1_STORE_ROOT_DIR . 'app/bootstrap.php';

                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $obj = $bootstrap->getObjectManager();

                        $state = $obj->get('Magento\Framework\App\State');
                    }

                    switch ($_POST['data_notification']['method']) {
                        case 'order.update':
                            if (!file_exists(M1_STORE_ROOT_DIR . '/app/etc/env.php')) {
                                $order = Mage::getModel('sales/order')->load($_POST['orderId']);
                                $order->sendOrderUpdateEmail(true, $_POST['data_notification']['comment']);
                                $order->save();

                                echo json_encode($response);
                            } else {
                                $state->setAreaCode('frontend');
                                $order = $obj->create('Magento\Sales\Model\Order')->load($_POST['orderId']); // this is entity id
                                $obj->create('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender')
                                    ->send($order, true, $_POST['data_notification']['comment']);

                                echo json_encode($response);
                            }

                            break;

                        case 'order.shipment.add':
                            if (!file_exists(M1_STORE_ROOT_DIR . '/app/etc/env.php')) {
                                $shipment = Mage::getModel('sales/order_shipment')
                                    ->loadByIncrementId($_POST['data_notification']['shipment_id']);
                                $shipment->sendEmail();
                                $shipment->save();

                                echo json_encode($response);
                            } else {
                                $state->setAreaCode('global');
                                $shipment = $obj->create('Magento\Sales\Model\Order\Shipment')
                                    ->loadByIncrementId($_POST['data_notification']['shipment_id']); // this is entity id
                                $obj->create('Magento\Sales\Model\Order\Email\Sender\ShipmentSender')
                                    ->send($shipment);

                                echo json_encode($response);
                            }

                            break;
                    }

                    break;

                case 'Prestashop' :
                    if (version_compare($bridge->config->cartVars['dbVersion'], '1.6.0', '>=')) {
                        define('PS_DIR', M1_STORE_BASE_DIR);

                        require_once PS_DIR . '/config/config.inc.php';

                        if (file_exists(PS_DIR . '/vendor/autoload.php')) {
                            require_once PS_DIR . '/vendor/autoload.php';
                        } else {
                            require_once PS_DIR . '/config/autoload.php';
                        }

                        $order = new Order($_POST['orderId']);
                        $customer = new Customer((int)$order->id_customer);

                        if (isset($_POST['data_notification']['comment'])) {
                            $varsTpl = array(
                                '{lastname}' => $customer->lastname,
                                '{firstname}' => $customer->firstname,
                                '{id_order}' => $order->id,
                                '{order_name}' => $order->getUniqReference(),
                                '{message}' => $_POST['data_notification']['comment']
                            );

                            $title = $this->_getTranslatedTitle($bridge->config->cartVars['dbVersion'], $order);
                            Mail::Send(
                                (int)$order->id_lang, 'order_merchant_comment',
                                $title, $varsTpl, $customer->email,
                                $customer->firstname . ' ' . $customer->lastname, null, null, null, null, PS_DIR . '/mails/', true, (int)$order->id_shop
                            );
                        }

                        if (isset($_POST['data_notification']['order_history_id'])) {
                            $history = new OrderHistory((int)Tools::getValue('id_order_history'));
                            $history->id = (int)$_POST['data_notification']['order_history_id'];

                            if (version_compare($bridge->config->cartVars['dbVersion'], '1.7.0', '>=')) {
                                $result = Db::getInstance()->getRow('
                  SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname,
                    c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`
                  FROM `' . _DB_PREFIX_ . 'order_history` oh
                    LEFT JOIN `' . _DB_PREFIX_ . 'orders` o
                      ON oh.`id_order` = o.`id_order`
                    LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                      ON o.`id_customer` = c.`id_customer`
                    LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os
                      ON oh.`id_order_state` = os.`id_order_state`
                    LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl
                      ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
                  WHERE oh.`id_order_history` = ' . (int)$_POST['data_notification']['order_history_id'] . '
                    AND os.`send_email` = 1');

                                if (isset($result['template']) && Validate::isEmail($result['email'])) {
                                    ShopUrl::cacheMainDomainForShop($order->id_shop);

                                    $topic = $result['osname'];
                                    $carrierUrl = '';
                                    if (Validate::isLoadedObject($carrier = new Carrier((int)$order->id_carrier, $order->id_lang))) {
                                        $carrierUrl = $carrier->url;
                                    }
                                    $data = array(
                                        '{lastname}' => $result['lastname'],
                                        '{firstname}' => $result['firstname'],
                                        '{id_order}' => (int)$order->id,
                                        '{order_name}' => $order->getUniqReference(),
                                        '{followup}' => str_replace('@', $order->getWsShippingNumber(), $carrierUrl),
                                        '{shipping_number}' => $order->getWsShippingNumber(),
                                    );

                                    if ($result['module_name']) {
                                        $module = Module::getInstanceByName($result['module_name']);
                                        if (Validate::isLoadedObject($module) && isset($module->extra_mail_vars) && is_array($module->extra_mail_vars)) {
                                            $data = array_merge($data, $module->extra_mail_vars);
                                        }
                                    }

                                    $currency = Currency::getCurrencyInstance((int)$order->id_currency);
                                    $currencySign = is_array($currency) ? $currency['sign'] : $currency->sign;

                                    $data['{total_paid}'] = $currencySign . (float)$order->total_paid;
                                    if (Validate::isLoadedObject($order)) {
                                        Mail::Send(
                                            (int)$order->id_lang,
                                            $result['template'],
                                            $topic,
                                            $data,
                                            $result['email'],
                                            $result['firstname'] . ' ' . $result['lastname'],
                                            null,
                                            null,
                                            null,
                                            null,
                                            _PS_MAIL_DIR_,
                                            false,
                                            (int)$order->id_shop
                                        );
                                    }
                                }
                            } else {
                                $history->sendEmail($order, array());
                            }
                        }
                        echo json_encode($response);
                    }
                    break;
                case 'Woocommerce':

                    require_once M1_STORE_BASE_DIR . '/wp-load.php';

                    $msgClasses = $_POST['data_notification']['msg_classes'];
                    $callParams = $_POST['data_notification']['msg_params'];
                    $storeId = $_POST['data_notification']['store_id'];
                    if (function_exists('switch_to_blog')) {
                        switch_to_blog($storeId);
                    }
                    $emails = wc()->mailer()->get_emails();
                    foreach ($msgClasses as $msgClass) {
                        if (isset($emails[$msgClass])) {
                            call_user_func_array(array($emails[$msgClass], 'trigger'), $callParams[$msgClass]);
                        }
                    }
                    echo json_encode($response);
                    break;
            }
        } catch (Exception $e) {
            $response['error'] = true;
            $response['code'] = $e->getCode();
            $response['message'] = $e->getMessage();

            echo json_encode($response);
        }
    }

    protected function _getTranslatedTitle($cartVer, $order)
    {
        if (version_compare($cartVer, '1.7.0', '>=')) {
            $orderLanguage = new Language((int) $order->id_lang);
            return Context::getContext()->getTranslator()->trans(
                'New message regarding your order',
                array(),
                'Emails.Subject',
                $orderLanguage->locale
            );
        } else {
            return Mail::l('New message regarding your order', (int)$order->id_lang);
        }

    }
}


/**
 * Class M1_Bridge_Action_Savefile
 */
class M1_Bridge_Action_Savefile
{
    protected $_imageType = null;
    protected $_mageLoaded = false;

    /**
     * @param $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $source      = $_POST['src'];
        $destination = $_POST['dst'];
        $width       = (int)$_POST['width'];
        $height      = (int)$_POST['height'];

        echo $this->_saveFile($source, $destination, $width, $height);
    }

    /**
     * @param $source
     * @param $destination
     * @param $width
     * @param $height
     * @param string $local
     * @return string
     */
    public function _saveFile($source, $destination, $width, $height)
    {
        if (preg_match('/(.png)|(.jpe?g)|(.gif)$/', $destination) != 1) {
            die('ERROR_INVALID_FILE_EXTENSION');
        }

        if (!preg_match('/^https?:\/\//i', $source)) {
            $result = $this->_createFile($source, $destination);
        } else {
            $result = $this->_saveFileCurl($source, $destination);
        }

        if ($result != "OK") {
            return $result;
        }

        $destination = M1_STORE_BASE_DIR . $destination;

        if ($width != 0 && $height != 0) {
            $this->_scaled2( $destination, $width, $height );
        }

        if ($this->cartType == "Prestashop11") {
            // convert destination.gif(png) to destination.jpg
            $imageGd = $this->_loadImage($destination);

            if ($imageGd === false) {
                return $result;
            }

            if (!$this->_convert($imageGd, $destination, IMAGETYPE_JPEG, 'jpg')) {
                return "CONVERT FAILED";
            }
        }

        return $result;
    }

    /**
     * @param $filename
     * @param bool $skipJpg
     * @return bool|resource
     */
    private function _loadImage($filename, $skipJpg = true)
    {
        $imageInfo = @getimagesize($filename);
        if ($imageInfo === false) {
            return false;
        }

        $this->_imageType = $imageInfo[2];

        switch ($this->_imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($filename);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($filename);
                break;
            default:
                return false;
        }

        if ($skipJpg && ($this->_imageType == IMAGETYPE_JPEG)) {
            return false;
        }

        return $image;
    }

    /**
     * @param $image
     * @param $filename
     * @param int $imageType
     * @param int $compression
     * @return bool
     */
    private function _saveImage($image, $filename, $imageType = IMAGETYPE_JPEG, $compression = 85)
    {
        $result = true;
        if ($imageType == IMAGETYPE_JPEG) {
            $result = imagejpeg($image, $filename, $compression);
        } elseif ($imageType == IMAGETYPE_GIF) {
            $result = imagegif($image, $filename);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $result = imagepng($image, $filename);
        }

        imagedestroy($image);

        return $result;
    }

    /**
     * @param $source
     * @param $destination
     * @return string
     */
    private function _createFile($source, $destination)
    {
        if ($this->_createDir(dirname($destination)) !== false) {
            $destination = M1_STORE_BASE_DIR . $destination;
            $body = base64_decode($source);
            if ($body === false || file_put_contents($destination, $body) === false) {
                return '[BRIDGE ERROR] File save failed!';
            }

            return 'OK';
        }

        return '[BRIDGE ERROR] Directory creation failed!';
    }

    /**
     * @param $source
     * @param $destination
     * @return string
     */
    private function _saveFileCurl($source, $destination)
    {
        $source = $this->_escapeSource($source);
        if ($this->_createDir(dirname($destination)) !== false) {
            $destination = M1_STORE_BASE_DIR . $destination;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $httpResponseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpResponseCode != 200) {
                curl_close($ch);
                return "[BRIDGE ERROR] Bad response received from source, HTTP code $httpResponseCode!";
            }

            $dst = @fopen($destination, "wb");
            if ($dst === false) {
                return "[BRIDGE ERROR] Can't create  $destination!";
            }
            curl_setopt($ch, CURLOPT_NOBODY, false);
            curl_setopt($ch, CURLOPT_FILE, $dst);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_exec($ch);
            if (($error_no = curl_errno($ch)) != CURLE_OK) {
                return "[BRIDGE ERROR] $error_no: " . curl_error($ch);
            }

            curl_close($ch);
            return "OK";

        } else {
            return "[BRIDGE ERROR] Directory creation failed!";
        }
    }

    /**
     * @param $source
     * @return mixed
     */
    private function _escapeSource($source)
    {
        return str_replace(" ", "%20", $source);
    }

    /**
     * @param $dir
     * @return bool
     */
    private function _createDir($dir)
    {
        $dirParts = explode("/", $dir);
        $path = M1_STORE_BASE_DIR;
        foreach ($dirParts as $item) {
            if ($item == '') {
                continue;
            }
            $path .= $item . DIRECTORY_SEPARATOR;
            if (!is_dir($path)) {
                $res = @mkdir($path, 0755);
                if (!$res) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param resource $image     - GD image object
     * @param string   $filename  - store sorce pathfile ex. M1_STORE_BASE_DIR . '/img/c/2.gif';
     * @param int      $type      - IMAGETYPE_JPEG, IMAGETYPE_GIF or IMAGETYPE_PNG
     * @param string   $extension - file extension, this use for jpg or jpeg extension in prestashop
     *
     * @return true if success or false if no
     */
    private function _convert($image, $filename, $type = IMAGETYPE_JPEG, $extension = '')
    {
        $end = pathinfo($filename, PATHINFO_EXTENSION);

        if ($extension == '') {
            $extension = image_type_to_extension($type, false);
        }

        if ($end == $extension) {
            return true;
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        $newImage = imagecreatetruecolor($width, $height);

        /* Allow to keep nice look even if resized */
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $white);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width, $height );
        imagecolortransparent($newImage, $white);

        $pathSave = rtrim($filename, $end);

        $pathSave .= $extension;

        return $this->_saveImage($newImage, $pathSave, $type);
    }

    /**
     * scaled2 method optimizet for prestashop
     *
     * @param $destination
     * @param $destWidth
     * @param $destHeight
     * @return string
     */
    private function _scaled2($destination, $destWidth, $destHeight)
    {
        $method = 0;

        $sourceImage = $this->_loadImage($destination, false);

        if ($sourceImage === false) {
            return "IMAGE NOT SUPPORTED";
        }

        $sourceWidth  = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $widthDiff = $destWidth / $sourceWidth;
        $heightDiff = $destHeight / $sourceHeight;

        if ($widthDiff > 1 && $heightDiff > 1) {
            $nextWidth = $sourceWidth;
            $nextHeight = $sourceHeight;
        } else {
            if (intval($method) == 2 || (intval($method) == 0 AND $widthDiff > $heightDiff)) {
                $nextHeight = $destHeight;
                $nextWidth = intval(($sourceWidth * $nextHeight) / $sourceHeight);
                $destWidth = ((intval($method) == 0 ) ? $destWidth : $nextWidth);
            } else {
                $nextWidth = $destWidth;
                $nextHeight = intval($sourceHeight * $destWidth / $sourceWidth);
                $destHeight = (intval($method) == 0 ? $destHeight : $nextHeight);
            }
        }

        $borderWidth = intval(($destWidth - $nextWidth) / 2);
        $borderHeight = intval(($destHeight - $nextHeight) / 2);

        $destImage = imagecreatetruecolor($destWidth, $destHeight);

        $white = imagecolorallocate($destImage, 255, 255, 255);
        imagefill($destImage, 0, 0, $white);

        imagecopyresampled($destImage, $sourceImage, $borderWidth, $borderHeight, 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight);
        imagecolortransparent($destImage, $white);

        return $this->_saveImage($destImage, $destination, $this->_imageType, 100) ? "OK" : "CAN'T SCALE IMAGE";
    }
}

/**
 * Class M1_Bridge_Action_ReindexProduct
 */
class M1_Bridge_Action_ReindexProduct
{

    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => false);
        switch ($bridge->config->cartType) {
            case 'Magento1212':
                $productId = $_POST['product_id'];

                try {
                    $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);

                    if (version_compare($version, '2.0.0', '<')) {
                        require M1_STORE_ROOT_DIR . '/app/Mage.php';

                        Mage::app();
                        $product = Mage::getModel('catalog/product')->load($productId);
                        $event = Mage::getSingleton('index/indexer')->logEvent(
                            $product,
                            $product->getResource()->getType(),
                            Mage_Index_Model_Event::TYPE_SAVE,
                            false
                        );
                        $indexCollection = Mage::getModel('index/process')->getCollection();
                        foreach ($indexCollection as $index) {
                            $index->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->processEvent($event);
                        }

                    } else {
                        $_SERVER['REQUEST_URI'] = '';
                        require M1_STORE_ROOT_DIR . '/app/bootstrap.php';

                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $objectManager = $bootstrap->getObjectManager();
                        $state = $objectManager->get('\Magento\Framework\App\State');
                        $state->setAreaCode('frontend'); //required

                        $indexes = array(
                            'Magento\CatalogInventory\Model\Indexer\Stock',
                            'Magento\Catalog\Model\Indexer\Product\Price',
                            'Magento\Catalog\Model\Indexer\Product\Eav',
                            'Magento\Catalog\Model\Indexer\Product\Category',
                        );

                        /**
                         * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                         */
                        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
                        if ($scopeConfig->getValue('catalog/frontend/flat_catalog_product', 'default')) {
                            $indexes[] = 'Magento\Catalog\Model\Indexer\Product\Flat';
                        }

                        /**
                         * @var Magento\Framework\Indexer\ActionInterface $indexer
                         */
                        foreach ($indexes as $index) {
                            $indexer = $objectManager->create($index);
                            $indexer->executeRow($productId);
                        }

                        /** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
                        $indexerRegistry = $objectManager->get('\Magento\Framework\Indexer\IndexerRegistry');
                        $indexerRegistry->get(Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->reindexRow($productId);
                    }

                    $response['data'] = true;

                } catch (Exception $e) {
                    $response['error']['message'] = $e->getMessage();
                    $response['error']['code'] = $e->getCode();
                }

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }

}



/**
 * Class M1_Bridge_Action_Query
 */
class M1_Bridge_Action_Query
{

    /**
     * Extract extended query params from post and request
     */
    public static function requestToExtParams()
    {
        return array(
            'fetch_fields' => (isset($_POST['fetchFields']) && ($_POST['fetchFields'] == 1)),
            'set_names' => isset($_REQUEST['set_names']) ? $_REQUEST['set_names'] : false
        );
    }

    /**
     * @param M1_Bridge $bridge Bridge Instance
     *
     * @return bool
     */
    public static function setSqlMode(M1_Bridge $bridge)
    {
        if (isset($_REQUEST['sql_settings'])) {
            $sqlSettings = $_REQUEST['sql_settings'];

            try {
                if (isset($sqlSettings['sql_modes'])) {
                    $query = "SET SESSION SQL_MODE={$sqlSettings['sql_modes']}";
                    $bridge->getLink()->localQuery($query);
                }

                if (isset($sqlSettings['sql_variables'])) {
                    $query = $sqlSettings['sql_variables'];
                    $bridge->getLink()->localQuery($query);
                }
            } catch (Throwable $exception) {
                echo base64_encode(
                    serialize(
                        [
                            'error'         => $exception->getMessage(),
                            'query'         => $query,
                            'failedQueryId' => 0,
                        ]
                    )
                );

                return false;
            }
        }

        return true;
    }

    /**
     * @param M1_Bridge $bridge Bridge instance
     * @return bool
     */
    public function perform(M1_Bridge $bridge)
    {
        if (isset($_POST['query']) && isset($_POST['fetchMode'])) {
            $query = base64_decode(swapLetters($_POST['query']));

            $fetchMode = (int)$_POST['fetchMode'];

            if (!self::setSqlMode($bridge)) {
                return false;
            }

            $res = $bridge->getLink()->query($query, $fetchMode, self::requestToExtParams());

            if (is_array($res['result']) || is_bool($res['result'])) {
                $result = serialize(array(
                    'res'           => $res['result'],
                    'fetchedFields' => @$res['fetchedFields'],
                    'insertId'      => $bridge->getLink()->getLastInsertId(),
                    'affectedRows'  => $bridge->getLink()->getAffectedRows(),
                ));

                echo base64_encode($result);
            } else {
                echo base64_encode($res['message']);
            }
        } else {
            return false;
        }
    }
}

class M1_Bridge_Action_Platform_Action
{
    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        if (isset($_POST['platform_action'], $_POST['data'])
            && $_POST['platform_action']
            && method_exists($bridge->config, $_POST['platform_action'])
        ) {
            $response = array('error' => null, 'data' => null);

            try {
                $data = json_decode(base64_decode(swapLetters($_POST['data'])), true);
                $response['data'] = $bridge->config->{$_POST['platform_action']}($data);
            } catch (Exception $e) {
                $response['error']['message'] = $e->getMessage();
                $response['error']['code'] = $e->getCode();
            } catch (Throwable $e) {
                $response['error']['message'] = $e->getMessage();
                $response['error']['code'] = $e->getCode();
            }

            echo json_encode($response);
        } else {
            echo json_encode(array('error' => array('message' => 'Action is not supported'), 'data' => null));
        }
    }
}

/**
 * Class M1_Bridge_Action_Phpinfo
 */
class M1_Bridge_Action_Phpinfo
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        phpinfo();
    }
}


/**
 * Class M1_Bridge_Action_Mysqlver
 */
class M1_Bridge_Action_Mysqlver
{

    /**
     * @param $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $message = array();
        preg_match('/^(\d+)\.(\d+)\.(\d+)/', mysql_get_server_info($bridge->getLink()), $message);
        echo sprintf("%d%02d%02d", $message[1], $message[2], $message[3]);
    }
}

class M1_Bridge_Action_Multiquery
{

    protected $_lastInsertIds = array();
    protected $_result        = array();

    /**
     * @param M1_Bridge $bridge Bridge Instance
     * @return bool|null
     */
    public function perform(M1_Bridge $bridge)
    {
        if (isset($_POST['queries']) && isset($_POST['fetchMode'])) {
            @ini_set("memory_limit", "512M");

            $queries = json_decode(base64_decode(swapLetters($_POST['queries'])));
            $count = 0;

            if (!M1_Bridge_Action_Query::setSqlMode($bridge)) {
                return false;
            }

            foreach ($queries as $queryId => $query) {

                if ($count++ > 0) {
                    $query = preg_replace_callback('/_A2C_LAST_\{([a-zA-Z0-9_\-]{1,32})\}_INSERT_ID_/', array($this, '_replace'), $query);
                    $query = preg_replace_callback('/A2C_USE_FIELD_\{([\w\d\s\-]+)\}_FROM_\{([a-zA-Z0-9_\-]{1,32})\}_QUERY/', array($this, '_replaceWithValues'), $query);
                }

                $res = $bridge->getLink()->query($query, (int)$_POST['fetchMode'], M1_Bridge_Action_Query::requestToExtParams());
                if (is_array($res['result']) || is_bool($res['result'])) {

                    $queryRes = array(
                        'res'           => $res['result'],
                        'fetchedFields' => @$res['fetchedFields'],
                        'insertId'      => $bridge->getLink()->getLastInsertId(),
                        'affectedRows'  => $bridge->getLink()->getAffectedRows(),
                    );

                    $this->_result[$queryId] = $queryRes;
                    $this->_lastInsertIds[$queryId] = $queryRes['insertId'];

                } else {
                    $data['error'] = $res['message'];
                    $data['failedQueryId'] = $queryId;
                    $data['query'] = $query;

                    echo base64_encode(serialize($data));
                    return false;
                }
            }
            echo base64_encode(serialize($this->_result));
        } else {
            return false;
        }
    }

    /**
     * @param array $matches Matches
     *
     * @return int|string
     */
    protected function _replace($matches)
    {
        return $this->_lastInsertIds[$matches[1]];
    }

    /**
     * @param array $matches Matches
     *
     * @return string
     */
    protected function _replaceWithValues($matches)
    {
        $values = array();
        if (isset($this->_result[$matches[2]]['res'])) {
            foreach ($this->_result[$matches[2]]['res'] as $row) {
                $values[] = addslashes($row[$matches[1]]);
            }
        }

        return '"' . implode('","', array_unique($values)) . '"';
    }

}

/**
 * Class M1_Bridge_Action_m2eExtensionNotify
 */
class M1_Bridge_Action_m2eExtensionNotify
{
    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);

        try {
            $productId = $_POST['product_id'];

            $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
            if (version_compare($version, '2.0.0', '<')) {
                require M1_STORE_ROOT_DIR . '/app/Mage.php';
                Mage::app();
                $model = Mage::getModel('M2ePro/PublicServices_Product_SqlChange');
            } else {
                require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                $objectManager = $bootstrap->getObjectManager();
                $model = $objectManager->create('\Ess\M2ePro\PublicServices\Product\SqlChange');
            }
            $model->markProductChanged($productId);
            $model->applyChanges();
            $response['data'] = 'OK';
        } catch (Exception $e) {
            $response['error']['message'] = $e->getMessage();
            $response['error']['code']    = $e->getCode();
        }

        echo json_encode($response);
    }
}

/**
 * Class M1_Bridge_Action_Getconfig
 */
class M1_Bridge_Action_Getconfig
{

    /**
     * @param $val
     * @return int
     */
    private function parseMemoryLimit($val)
    {
        $valInt = (int)$val;
        $last = strtolower($val[strlen($val)-1]);

        switch($last) {
            case 'g':
                $valInt *= 1024;
            case 'm':
                $valInt *= 1024;
            case 'k':
                $valInt *= 1024;
        }

        return $valInt;
    }

    /**
     * @return mixed
     */
    private function getMemoryLimit()
    {
        $memoryLimit = trim(@ini_get('memory_limit'));
        if (strlen($memoryLimit) === 0) {
            $memoryLimit = "0";
        }
        $memoryLimit = $this->parseMemoryLimit($memoryLimit);

        $maxPostSize = trim(@ini_get('post_max_size'));
        if (strlen($maxPostSize) === 0) {
            $maxPostSize = "0";
        }
        $maxPostSize = $this->parseMemoryLimit($maxPostSize);

        $suhosinMaxPostSize = trim(@ini_get('suhosin.post.max_value_length'));
        if (strlen($suhosinMaxPostSize) === 0) {
            $suhosinMaxPostSize = "0";
        }
        $suhosinMaxPostSize = $this->parseMemoryLimit($suhosinMaxPostSize);

        if ($suhosinMaxPostSize == 0) {
            $suhosinMaxPostSize = $maxPostSize;
        }

        if ($maxPostSize == 0) {
            $suhosinMaxPostSize = $maxPostSize = $memoryLimit;
        }

        return min($suhosinMaxPostSize, $maxPostSize, $memoryLimit);
    }

    /**
     * @return bool
     */
    private function isZlibSupported()
    {
        return function_exists('gzdecode');
    }

    /**
     * @param $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        if (!defined("DEFAULT_LANGUAGE_ISO2")) {
            define("DEFAULT_LANGUAGE_ISO2", ""); //variable for Interspire cart
        }

        try {
            $timeZone = date_default_timezone_get();
        } catch (Exception $e) {
            $timeZone = 'UTC';
        }

        $result = array(
            "images" => array(
                "imagesPath"                => $bridge->config->imagesDir, // path to images folder - relative to store root
                "categoriesImagesPath"      => $bridge->config->categoriesImagesDir,
                "categoriesImagesPaths"     => $bridge->config->categoriesImagesDirs,
                "productsImagesPath"        => $bridge->config->productsImagesDir,
                "productsImagesPaths"       => $bridge->config->productsImagesDirs,
                "manufacturersImagesPath"   => $bridge->config->manufacturersImagesDir,
                "manufacturersImagesPaths"  => $bridge->config->manufacturersImagesDirs,
            ),
            "languages"             => $bridge->config->languages,
            "baseDirFs"             => M1_STORE_BASE_DIR,    // filesystem path to store root
            "bridgeVersion"         => M1_BRIDGE_VERSION,
            "defaultLanguageIso2"   => DEFAULT_LANGUAGE_ISO2,
            "databaseName"          => $bridge->config->dbname,
            "cartDbPrefix"          => $bridge->config->tblPrefix,
            "memoryLimit"           => $this->getMemoryLimit(),
            "zlibSupported"         => $this->isZlibSupported(),
            "cartVars"              => $bridge->config->cartVars,
            "time_zone"             => $bridge->config->timeZone ?: $timeZone
        );

        echo serialize($result);
    }

}

/**
 * Class M1_Bridge_Action_Collect_Totals
 */
class M1_Bridge_Action_Get_Url
{

    /**
     * @param M1_Bridge $bridge bridge
     *
     * @return void
     */
    public function perform(M1_Bridge $bridge)
    {
        $responce = array(
            'data' => null,
            'error' => false,
            'code' => null,
            'message' => null,
        );

        try {
            switch ($_POST['cart_id']) {
                case 'Prestashop':
                    define ('PS_DIR', M1_STORE_BASE_DIR);
                    require_once PS_DIR .'/config/config.inc.php';

                    if (file_exists(PS_DIR . '/vendor/autoload.php')) {
                        require_once PS_DIR . '/vendor/autoload.php';
                    } else {
                        require_once PS_DIR . '/config/autoload.php';
                    }

                    $linkCore = new LinkCore();

                    $version = $bridge->config->cartVars['dbVersion'];

                    foreach ($_POST['productData'] as $productId => $attribute) {

                        if (version_compare($version, '1.7.0', '<')) {
                            $attribute = 0;
                        }

                        $links[$productId] = $linkCore->getProductLink(
                            $productId,
                            null,
                            null,
                            null,
                            $_POST['langId'],
                            $_POST['storeId'],
                            $attribute
                        );
                    }

                    $responce['data'] = json_encode($links);

                    echo json_encode($responce);
                    break;
            }
        } catch (Exception $e) {
            $responce['error'] = true;
            $responce['code'] = $e->getCode();
            $responce['message'] = $e->getMessage();
            echo json_encode($responce);
        }
    }

}


/**
 * Class M1_Bridge_Action_GetShipmentProviders
 */
class M1_Bridge_Action_GetShipmentProviders
{

    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);

        switch ($bridge->config->cartType) {

            case 'Wordpress':

                if ($bridge->config->cartId === 'Woocommerce') {

                    if (file_exists(M1_STORE_BASE_DIR . 'wp-content/plugins/woocommerce-shipment-tracking/includes/class-wc-shipment-tracking.php')) {
                        try {
                            require_once M1_STORE_BASE_DIR . 'wp-load.php';
                            require_once M1_STORE_BASE_DIR . 'wp-content/plugins/woocommerce-shipment-tracking/includes/class-wc-shipment-tracking.php';

                            $st = new WC_Shipment_Tracking_Actions();
                            $res = $st->get_providers();
                            $data = array();

                            foreach ($res as $country => $providers) {
                                foreach ($providers as $providerName => $url) {
                                    $data[sanitize_title($providerName)] = array(
                                        'name' => $providerName,
                                        'country' => $country,
                                        'url' => $url
                                    );
                                }
                            }

                            $response['data'] = $data;

                        } catch (Exception $e) {
                            $response['error']['message'] = $e->getMessage();
                            $response['error']['code'] = $e->getCode();
                        }
                    } else {
                        $response['error']['message'] = 'File does not exist';
                    }

                } else {
                    $response['error']['message'] = 'Action is not supported';
                }

                break;

            case 'Magento1212':
                try {

                    $storeId = isset($_POST['store_id']) ? $_POST['store_id'] : 0;

                    $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                    if (version_compare($version, '2.0.0', '<')) {
                        require M1_STORE_ROOT_DIR . '/app/Mage.php';
                        Mage::app();
                        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();

                    } else {
                        require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $objectManager = $bootstrap->getObjectManager();

                        $state = $objectManager->get('\Magento\Framework\App\State');
                        $state->setAreaCode('frontend');

                        $carriers = $objectManager->create('Magento\Shipping\Model\Config')->getAllCarriers();
                        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
                    }

                    $res = array();

                    foreach ($carriers as $carrierCode => $carrier) {
                        if (version_compare($version, '2.0.0', '<')) {
                            $carrierTitle = Mage::getStoreConfig("carriers/$carrierCode/title", $storeId);
                            $carrierActive = Mage::getStoreConfig("carriers/$carrierCode/active", $storeId);
                        } else {
                            $carrierTitle = $scopeConfig->getValue("carriers/$carrierCode/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                            $carrierActive = $scopeConfig->getValue("carriers/$carrierCode/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
                        }

                        $methodsData = array();

                        try {
                            $methods = $carrier->getAllowedMethods();
                        } catch (Exception $e) {
                            $methods = array();
                        }

                        foreach ($methods as $methodCode => $method) {
                            $code = $carrierCode . '_' . $methodCode;
                            $methodsData[] = array('code' => $code, 'title' => $method);
                        }

                        $res[] = array('methods' => $methodsData, 'code' => $carrierCode, 'title' => $carrierTitle, 'active' => $carrierActive);
                    }

                    $response['data'] = $res;

                } catch (Exception $e) {
                    $response['error']['message'] = $e->getMessage();
                    $response['error']['code']    = $e->getCode();
                }

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }

}

/**
 * Class M1_Bridge_Action_GetPaymentModules
 */
class M1_Bridge_Action_GetPaymentModules
{

    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => array());

        switch ($bridge->config->cartType) {

            case 'Prestashop':

                try {

                    if (version_compare($bridge->config->cartVars['dbVersion'], '1.6.0', '>=')) {

                        if (@include_once(M1_STORE_BASE_DIR .'config/config.inc.php')) {

                            if (isset($_POST['store_id'])) {
                                Shop::setContext(Shop::CONTEXT_SHOP, $_POST['store_id']);
                            } else {
                                Shop::setContext(Shop::CONTEXT_ALL);
                            }

                            $modules_list = PaymentModule::getInstalledPaymentModules();

                            foreach($modules_list as $module) {
                                $response['data'][$module['name']] = array(
                                    'display_name' => Module::getModuleName($module['name']),
                                    'name' => $module['name']
                                );
                            }
                        }
                    }

                } catch (Exception $e) {
                    $response['error']['message'] = $e->getMessage();
                    $response['error']['code'] = $e->getCode();
                }

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }

}

/**
 * Class M1_Bridge_Action_GetCartWeight
 */
class M1_Bridge_Action_GetCartWeight
{
    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);
        switch ($bridge->config->cartType) {
            case 'Magento1212':
                $quoteId = $_POST['quote_id'];
                $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                if (version_compare($version, '2.0.0', '<')) {
                    require M1_STORE_ROOT_DIR . '/app/Mage.php';
                    Mage::app();
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                } else {
                    require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                    $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                    $objectManager = $bootstrap->getObjectManager();

                    $state = $objectManager->get('\Magento\Framework\App\State');
                    $state->setAreaCode('frontend');

                    $quoteRepo = $objectManager->create('\Magento\Quote\Model\QuoteRepository');
                    $quote = $quoteRepo->get($quoteId);
                }

                $items = $quote->getAllItems();

                $weight = 0;
                foreach ($items as $item) {
                    $weight += ($item->getWeight() * $item->getQty());
                }

                $response['data'] = $weight;

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }
}

/**
 * Class M1_Bridge_Action_GetAbandonedOrderTotal
 */
class M1_Bridge_Action_GetAbandonedOrderTotal
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {

        $response = array('error' => null, 'data' => null);

        try {
            if (!empty($_POST['cartIds']) && $_POST['langId']) {

                include_once M1_STORE_BASE_DIR . '/config/config.inc.php';
                include_once M1_STORE_BASE_DIR . '/init.php';

                foreach ($_POST['cartIds'] as $cartId) {
                    $cart     = new Cart($cartId, $_POST['langId']);
                    $context  = new Context();
                    $currency = new Currency($cart->id_currency);

                    $context::getContext()->currency = $currency;

                    $response['data'][$cartId] = $cart->getSummaryDetails($_POST['langId'], false);
                }

                echo json_encode($response);
            }
        } catch (Exception $e) {
            $response['error']['message'] = $e->getMessage();
            $response['error']['code']    = $e->getCode();

            echo json_encode($response);
        }
    }

}

/**
 * Class M1_Bridge_Action_DispatchCartEvents
 */
class M1_Bridge_Action_DispatchCartEvents
{

    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);

        try {
            switch ($bridge->config->cartType) {
                case 'Magento1212':

                    $events = json_decode($_POST['events'], true);
                    if (is_array($events)) {

                        $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                        if (version_compare($version, '2.0.0', '<')) {
                            require M1_STORE_ROOT_DIR . '/app/Mage.php';
                            Mage::app();
                            $mageVersion = 1;
                        } else {
                            require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                            $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                            $objectManager = $bootstrap->getObjectManager();
                            $state = $objectManager->get('\Magento\Framework\App\State');
                            $state->setAreaCode('frontend');

                            $eventManager = $objectManager->create('Magento\Framework\Event\Manager');
                            $mageVersion = 2;
                        }

                        foreach ($events as $eventId => $eventParams) {
                            foreach ($eventParams as $paramKey => $paramData) {
                                if (isset($paramData['mage_model_name']) && isset($paramData['mage_object_id'])) {
                                    if ($mageVersion === 2) {
                                        $model = $objectManager->create($paramData['mage_model_name'])->load($paramData['mage_object_id']);
                                    } elseif ($mageVersion === 1) {
                                        $model = Mage::getModel($paramData['mage_model_name'])->load($paramData['mage_object_id']);
                                    } else {
                                        $model = null;
                                    }
                                    $eventParams[$paramKey] = $model;
                                } else {
                                    $eventParams[$paramKey] = $paramData['data'];
                                }
                            }

                            if ($mageVersion === 2) {
                                $eventManager->dispatch($eventId, $eventParams);
                            } elseif ($mageVersion === 1) {
                                Mage::dispatchEvent($eventId, $eventParams);
                            }

                        }
                        $response['data'] = true;
                    } else {
                        $response['error']['message'] = 'Invalid action params';
                    }

                    break;

                default:
                    $response['error']['message'] = 'Action is not supported';
            }
        } catch (Exception $e) {
            $response['error']['message'] = $e->getMessage();
            $response['error']['code']    = $e->getCode();
        }

        echo json_encode($response);
    }

}

/**
 * Class M1_Bridge_Action_Deleteimages
 */
class M1_Bridge_Action_Deleteimages
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        switch($bridge->config->cartType) {
            case "Pinnacle361":
                $this->_pinnacleDeleteImages($bridge);
                break;
            case "Prestashop11":
                $this->_prestaShopDeleteImages($bridge);
                break;
            case 'Summercart3' :
                $this->_summercartDeleteImages($bridge);
                break;
        }
    }

    /**
     * @param $bridge
     */
    private function _pinnacleDeleteImages(M1_Bridge $bridge)
    {
        $dirs = array(
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'catalog/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'manufacturers/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'products/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'products/thumbs/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'products/secondary/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'products/preview/',
        );

        $ok = true;

        foreach ($dirs as $dir) {

            if (!file_exists($dir)) {
                continue;
            }

            $dirHandle = opendir($dir);

            while (false !== ($file = readdir($dirHandle))) {
                if ($file != "." && $file != ".." && !preg_match("/^readme\.txt?$/",$file) && !preg_match("/\.bak$/i",$file)) {
                    $file_path = $dir . $file;
                    if( is_file($file_path) ) {
                        if(!rename($file_path, $file_path.".bak")) $ok = false;
                    }
                }
            }

            closedir($dirHandle);

        }

        if ($ok) print "OK";
        else print "ERROR";
    }

    /**
     * @param $bridge
     */
    private function _prestaShopDeleteImages(M1_Bridge $bridge)
    {
        $dirs = array(
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'c/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'p/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'm/',
        );

        $ok = true;

        foreach ($dirs as $dir) {

            if (!file_exists($dir)) {
                continue;
            }

            $dirHandle = opendir($dir);

            while (false !== ($file = readdir($dirHandle))) {
                if ($file != "." && $file != ".." && preg_match("/(\d+).*\.jpg?$/", $file)) {
                    $file_path = $dir . $file;
                    if (is_file($file_path)) {
                        if (!rename($file_path, $file_path . ".bak")) $ok = false;
                    }
                }
            }

            closedir($dirHandle);

        }

        if ($ok) print "OK";
        else print "ERROR";
    }

    /**
     * @param $bridge
     */
    private function _summercartDeleteImages(M1_Bridge $bridge)
    {
        $dirs = array(
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'categoryimages/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'manufacturer/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'productimages/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'productthumbs/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'productboximages/',
            M1_STORE_BASE_DIR . $bridge->config->imagesDir . 'productlargeimages/',
        );

        $ok = true;

        foreach ($dirs as $dir) {

            if (!file_exists($dir)) {
                continue;
            }

            $dirHandle = opendir($dir);

            while (false !== ($file = readdir($dirHandle))) {
                if (($file != ".") && ($file != "..") && !preg_match("/\.bak$/i",$file) ) {
                    $file_path = $dir . $file;
                    if( is_file($file_path) ) {
                        if(!rename($file_path, $file_path.".bak")) $ok = false;
                    }
                }
            }

            closedir($dirHandle);

        }

        if ($ok) print "OK";
        else print "ERROR";
    }
}

/**
 * Class M1_Bridge_Action_Delete
 */
class M1_Bridge_Action_Delete
{

    const ERROR_NONE = 0;
    const ERROR_FILES_ARE_NOT_WRITABLE = 1;
    const ERROR_CUSTOM_FILES_DETECTED = 2;
    const ERROR_INTERNAL_ERROR = 3;

    private $_response = array(
        'code' => self::ERROR_NONE,
        'message' => '',
    );

    /**
     * @param int    $code Response Code
     * @param string $msg  Response Msg
     */
    private function _response($code, $msg)
    {
        $this->_response['code'] = $code;
        $this->_response['message'] = $msg;

        die(json_encode($this->_response));
    }

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $allowedEntries = array(
            '.',
            '..',
            '.htaccess',
            'bridge.php',
            'config.php',
            'index.php',
        );

        $entriesToDelete = array(
            '.htaccess',
            'bridge.php',
            'config.php',
            'index.php',
        );

        $currentEntries = scandir(__DIR__);

        if ($diff = array_diff($currentEntries, $allowedEntries)) {
            $this->_response(
                self::ERROR_CUSTOM_FILES_DETECTED,
                'Unexpected files detected (' . implode(', ', $diff) . '). The bridge will not be removed. Please delete custom files first.'
            );
        }

        foreach ($entriesToDelete as $key => $entry) {
            if (!file_exists($entry)) {
                unset($entriesToDelete[$key]);
                continue;
            }

            if (!is_writable($entry)) {
                $this->_response(
                    self::ERROR_FILES_ARE_NOT_WRITABLE,
                    'Bridge is not deleted! File \'' . $entry . '\' is not writable. Please set write permission or delete files manually.'
                );
            }
        }

        try {
            foreach ($entriesToDelete as $entry) {
                unlink(__DIR__ . DIRECTORY_SEPARATOR . $entry);
            }

            rmdir(__DIR__);
        } catch (Exception $e) {
            $this->_response(self::ERROR_INTERNAL_ERROR, 'Bridge is not deleted! Internal error: ' . $e->getMessage());
        } catch (Throwable $e) {
            $this->_response(self::ERROR_INTERNAL_ERROR, 'Bridge is not deleted! Internal error: ' . $e->getMessage());
        }

        $this->_response(self::ERROR_NONE, 'Deleted successfully.');
    }

}

/**
 * Class M1_Bridge_Action_Cubecart
 */
class M1_Bridge_Action_Cubecart
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $dirHandle = opendir(M1_STORE_BASE_DIR . 'language/');

        $languages = array();

        while ($dirEntry = readdir($dirHandle)) {
            if (!is_dir(M1_STORE_BASE_DIR . 'language/' . $dirEntry) || $dirEntry == '.'
                || $dirEntry == '..' || strpos($dirEntry, "_") !== false
            ) {
                continue;
            }

            $lang['id'] = $dirEntry;
            $lang['iso2'] = $dirEntry;

            $cnfile = "config.inc.php";

            if (!file_exists(M1_STORE_BASE_DIR . 'language/' . $dirEntry . '/'. $cnfile)) {
                $cnfile = "config.php";
            }

            if (!file_exists( M1_STORE_BASE_DIR . 'language/' . $dirEntry . '/'. $cnfile)) {
                continue;
            }

            $str = file_get_contents(M1_STORE_BASE_DIR . 'language/' . $dirEntry . '/'.$cnfile);
            preg_match("/".preg_quote('$langName')."[\s]*=[\s]*[\"\'](.*)[\"\'];/", $str, $match);

            if (isset($match[1])) {
                $lang['name'] = $match[1];
                $languages[] = $lang;
            }
        }

        echo serialize($languages);
    }
}

/**
 * Class M1_Bridge_Action_CreateRefund
 */
class M1_Bridge_Action_CreateRefund
{

    private $_m2objectManager = null;

    /**
     * Check request key
     * @param string $requestKey Request Key
     * @return bool
     */
    private function _checkRequestKey($requestKey)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, M1_BRIDGE_CHECK_REQUEST_KEY_LINK);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('request_key' => $requestKey, 'store_key' => M1_TOKEN)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        try {
            $res = json_decode(curl_exec($ch));
        } catch (Exception $e) {
            return false;
        }

        curl_close($ch);

        return isset($res->success) && $res->success;
    }

    /**
     * @param stdClass $invoice Magento creditmemo object
     * @param array    $qtys    Array of item quantities ['order_item_id' => 'qty']
     * @return array
     */
    private function _adjustQuantitiesBeforeRefund($invoice, $qtys)
    {
        // fill missing quantities with zeros so they won't be fully refunded
        foreach ($invoice->getAllItems() as $item) {
            if (!isset($qtys[$item->getOrderItemId()])) {
                $qtys[$item->getOrderItemId()] = 0;
            }
        }
        return $qtys;
    }

    /**
     * @param stdClass $creditmemo Magento creditmemo object
     * @param array    $qtys       Array of item quantities ['order_item_id' => 'qty']
     * @return array
     */
    private function _adjustQuantitiesAfterRefund($creditmemo, $qtys)
    {
        // substract credit memo item quantities from quantity data
        // getItemByOrderId doesn't work at this point; use getAllItems instead
        foreach ($creditmemo->getAllItems() as $cmItem) {
            if (isset($qtys[$cmItem->getOrderItemId()])) {
                $qtys[$cmItem->getOrderItemId()] -= $cmItem->getQty();
                if ($qtys[$cmItem->getOrderItemId()] < 0) {
                    // this can happen for a parent item of a variant
                    // we don't need to pass quantity of a parent to createByInvoice; just reset it to 0
                    $qtys[$cmItem->getOrderItemId()] = 0;
                }
            }
        }
        return $qtys;
    }

    /**
     * @param M1_Bridge $bridge
     * @return void
     */
    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);

        if (!isset($_POST['request_key']) || !$this->_checkRequestKey($_POST['request_key'])) {
            $response['error']['message'] = 'Not authorized';
            echo json_encode($response);
            return;
        }

        $orderId = $_POST['order_id'];
        $isOnline = $_POST['is_online'];
        $refundMessage = isset($_POST['refund_message']) ? $_POST['refund_message'] : '';
        $itemsData = json_decode($_POST['items'], true);
        $totalRefund = isset($_POST['total_refund']) ? (float)$_POST['total_refund'] : null;
        $shippingRefund = isset($_POST['shipping_refund']) ? (float)$_POST['shipping_refund'] : null;
        $adjustmentRefund = isset($_POST['adjustment_refund']) ? (float)$_POST['adjustment_refund'] : null;
        $restockItems = isset($_POST['restock_items']) ? $_POST['restock_items'] : false;
        $sendNotifications = isset($_POST['send_notifications']) ? $_POST['send_notifications'] : false;

        try {

            switch ($bridge->config->cartType) {

                case 'Magento1212':

                    $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                    if (version_compare($version, '2.0.0', '<')) {
                        require M1_STORE_ROOT_DIR . '/app/Mage.php';
                        Mage::app();

                        $order = Mage::getModel('sales/order')->load($orderId);
                        $response['data']['order'] = $order->getIncrementId();

                        $orderService = Mage::getModel('sales/service_order', $order);
                        if ($restockItems) {
                            $stock = Mage::getModel('cataloginventory/stock');
                        } else {
                            $stock = null;
                        }

                        $qtys = array();
                        $processingFirstInvoice = true;
                        foreach ($itemsData as $itemData) {
                            $qtys[$itemData['order_product_id']] = isset($itemData['quantity']) ? (int)$itemData['quantity'] : 0;
                        }

                        $invoices = $order->getInvoiceCollection();
                        foreach ($invoices as $invoice) {
                            $response['data']['invoices'][] = $invoice->getIncrementId();

                            if ($qtys && (array_sum($qtys) === 0) && (!$processingFirstInvoice || (($shippingRefund === 0) && ($adjustmentRefund === 0)))) {
                                // if no items left for refund skip processing; collect invoice ids only
                                continue;
                            }

                            if (($qtys || $adjustmentRefund) && ($shippingRefund === null)) {
                                $shippingRefund = 0;
                                // if refund items requested and shipment not requested, do not refund shipment explicitly
                            }

                            if ($qtys || $shippingRefund || $adjustmentRefund) {
                                $qtys = $this->_adjustQuantitiesBeforeRefund($invoice, $qtys);
                                // if its a partial refund we must pass all item quantities to Magento method; zero if item won't be refunded
                            }

                            $mageRefundData = array('qtys' => $qtys);
                            if (($shippingRefund !== null) && $processingFirstInvoice) {
                                $mageRefundData['shipping_amount'] = $shippingRefund;
                            }

                            if (($adjustmentRefund !== null) && $processingFirstInvoice) {
                                if ($adjustmentRefund < 0) {
                                    $mageRefundData['adjustment_negative'] = -$adjustmentRefund;
                                } else {
                                    $mageRefundData['adjustment_positive'] = $adjustmentRefund;
                                }
                            }

                            $creditmemo = $orderService->prepareInvoiceCreditmemo($invoice, $mageRefundData);
                            if (!($creditmemo->getSubtotal() || $creditmemo->getShippingAmount() || $creditmemo->getAdjustment())){
                                // this invoice doesn't contain requested items; proceed to next one
                                continue;
                            }

                            if (!empty($refundMessage)) { // empty string raises exception
                                $creditmemo->addComment($refundMessage);
                            }

                            $creditmemo->setRefundRequested(true)
                                ->setOfflineRequested(!$isOnline)
                                ->register();
                            Mage::getModel('core/resource_transaction')
                                ->addObject($creditmemo)
                                ->addObject($creditmemo->getOrder())
                                ->addObject($creditmemo->getInvoice())
                                ->save();

                            if ($qtys) {
                                $qtys = $this->_adjustQuantitiesAfterRefund($creditmemo, $qtys);
                            }

                            if ($stock) {
                                foreach ($creditmemo->getAllItems() as $item) {
                                    $stock->backItemQty($item->getProductId(), $item->getQty());
                                }
                            }

                            if ($sendNotifications) {
                                $creditmemo->sendEmail();
                            }

                            $response['data']['refunds'][] = $creditmemo->getIncrementId();

                            $processingFirstInvoice = false;
                            // second and following invoices never contain any shipping

                        }
                    } else {

                        require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $this->_m2objectManager = $bootstrap->getObjectManager();

                        $state = $this->_m2objectManager->get('\Magento\Framework\App\State');
                        $state->setAreaCode('global');

                        $orderRepository = $this->_m2objectManager->create('\Magento\Sales\Model\OrderRepository');
                        $order = $orderRepository->get($orderId);

                        $response['data']['order'] = $order->getIncrementId();

                        $creditmemoService = $this->_m2objectManager->create('\Magento\Sales\Model\Service\CreditmemoService');
                        $creditmemoFactory = $this->_m2objectManager->create('\Magento\Sales\Model\Order\CreditmemoFactory');
                        $creditmemoRepository = $this->_m2objectManager->create('\Magento\Sales\Model\Order\CreditmemoRepository');

                        if ($sendNotifications) {
                            $creditmemoSender = $this->_m2objectManager->create('Magento\Sales\Model\Order\Email\Sender\CreditmemoSender');
                        } else {
                            $creditmemoSender = null;
                        }

                        $qtys = array();
                        foreach ($itemsData as $itemData) {
                            $qtys[$itemData['order_product_id']] = isset($itemData['quantity']) ? (int)$itemData['quantity'] : 0;
                        }

                        $invoices = $order->getInvoiceCollection();
                        $processingFirstInvoice = true;
                        foreach ($invoices as $invoice) {
                            $response['data']['invoices'][] = $invoice->getIncrementId();

                            if ($qtys && (array_sum($qtys) === 0) && (!$processingFirstInvoice || (($shippingRefund === 0) && ($adjustmentRefund === 0)))) {
                                // if no items left for refund skip processing; collect invoice ids only
                                continue;
                            }

                            if (($qtys || $adjustmentRefund) && ($shippingRefund === null)) {
                                $shippingRefund = 0;
                                // if refund items requested and shipment not requested, do not refund shipment explicitly
                            }

                            if ($qtys || $shippingRefund || $adjustmentRefund) {
                                $qtys = $this->_adjustQuantitiesBeforeRefund($invoice, $qtys);
                                // if its a partial refund we must pass all item quantities to Magento method; zero if item won't be refunded
                            }

                            $mageRefundData = array('qtys' => $qtys);
                            if (($shippingRefund !== null) && $processingFirstInvoice) {
                                $mageRefundData['shipping_amount'] = $shippingRefund;
                            }

                            if (($adjustmentRefund !== null) && $processingFirstInvoice) {
                                if ($adjustmentRefund < 0) {
                                    $mageRefundData['adjustment_negative'] = -$adjustmentRefund;
                                } else {
                                    $mageRefundData['adjustment_positive'] = $adjustmentRefund;
                                }
                            }

                            $creditmemo = $creditmemoFactory->createByInvoice($invoice, $mageRefundData);
                            if (!($creditmemo->getSubtotal() || $creditmemo->getShippingAmount() || $creditmemo->getAdjustment())){
                                // this invoice doesn't contain requested items; proceed to next one
                                continue;
                            }

                            if (!empty($refundMessage)) { // empty string raises exception
                                $creditmemo->addComment($refundMessage);
                            }

                            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                                $creditmemoItem->setBackToStock($restockItems);
                            }

                            $creditmemoService->refund($creditmemo, !$isOnline);
                            $creditmemoRepository->save($creditmemo);

                            if ($qtys) {
                                $qtys = $this->_adjustQuantitiesAfterRefund($creditmemo, $qtys);
                            }

                            if ($sendNotifications) {
                                $creditmemoSender->send($creditmemo);
                            }

                            $response['data']['refunds'][] = $creditmemo->getIncrementId();

                            $processingFirstInvoice = false;
                            // second and following invoices never contain any shipping

                        }

                        $orderRepository->save($order);
                    }

                    break;

                case 'Wordpress':

                    if ($bridge->config->cartId === 'Woocommerce') {

                        require_once M1_STORE_BASE_DIR . '/wp-load.php';

                        $order = wc_get_order($orderId);

                        if ($isOnline) {
                            if (WC()->payment_gateways()) {
                                $paymentGateways = WC()->payment_gateways->payment_gateways();
                            }

                            if (!(isset($paymentGateways[$order->payment_method]) && $paymentGateways[$order->payment_method]->supports('refunds'))) {
                                throw new Exception('Order payment method does not support refunds');
                            }
                        }

                        $refund = wc_create_refund(array(
                            'amount' => !is_null($totalRefund) ? (float)$totalRefund : $order->get_remaining_refund_amount(),
                            'reason' => $refundMessage,
                            'order_id' => $orderId,
                            'line_items' => $itemsData,
                            'refund_payment' => false, // dont repay refund immediately for better error processing
                            'restock_items' => $restockItems
                        ));

                        if (is_wp_error($refund)) {
                            $response['error']['code'] = $refund->get_error_code();
                            $response['error']['message'] = $refund->get_error_message();
                        } elseif (!$refund) {
                            $response['error']['message'] = 'An error occurred while attempting to create the refund';
                        }

                        if ($response['error']) {
                            echo json_encode($response);
                            return;
                        }

                        if ($isOnline) {

                            if (WC()->payment_gateways()) {
                                $paymentGateways = WC()->payment_gateways->payment_gateways();
                            }

                            if (isset($paymentGateways[$order->payment_method])
                                && $paymentGateways[$order->payment_method]->supports('refunds')
                            ) {
                                try {
                                    $result = $paymentGateways[$order->payment_method]->process_refund($orderId,
                                        $refund->get_refund_amount(), $refund->get_refund_reason());
                                } catch (Exception $e) {
                                    $refund->delete(true); // delete($force_delete = true)
                                    throw $e;
                                }
                                if (is_wp_error($result)) {
                                    $refund->delete(true);
                                    $response['error']['code'] = $result->get_error_code();
                                    $response['error']['message'] = $result->get_error_message();
                                } elseif (!$result) {
                                    $refund->delete(true);
                                    $response['error']['message'] = 'An error occurred while attempting to repay the refund using the payment gateway API';
                                } else {
                                    $response['data']['refunds'][] = $refund->get_id();
                                }
                            } else {
                                $refund->delete(true);
                                $response['error']['message'] = 'Order payment method does not support refunds';
                            }
                        }

                    } else {
                        $response['error']['message'] = 'Action is not supported';
                    }

                    break;

                default:
                    $response['error']['message'] = 'Action is not supported';
            }

        } catch (Exception $e) {
            unset($response['data']);
            $response['error']['message'] = $e->getMessage();
            $response['error']['code'] = $e->getCode();
        }

        echo json_encode($response);

    }

}


/**
 * Class M1_Bridge_Action_Collect_Totals
 */
class M1_Bridge_Action_Collect_Totals
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $responce = array(
            'error' => false,
            'code' => null,
            'message' => null,
        );

        try {
            switch ($_POST['cartId']) {
                case 'Magento1212' :
                    if (!file_exists(M1_STORE_ROOT_DIR . '/app/etc/env.php')) {

                        include_once M1_STORE_ROOT_DIR . 'includes/config.php';
                        include_once M1_STORE_ROOT_DIR . 'app/bootstrap.php';
                        include_once M1_STORE_ROOT_DIR . 'app/Mage.php';
                        Mage::init();

                        $quote = Mage::getModel('sales/quote');
                        $quote->loadActive($_POST['basketId']);
                        $quote->getShippingAddress();
                        $quote->collectTotals()->save();

                        echo json_encode($responce);
                        break;
                    } else {
                        include_once M1_STORE_ROOT_DIR . 'app/bootstrap.php';

                        $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                        $obj = $bootstrap->getObjectManager();

                        $state = $obj->get('Magento\Framework\App\State');
                        $state->setAreaCode('frontend');

                        $quote = $obj->get('Magento\Quote\Model\Quote');
                        $quote->loadActive($_POST['basketId']);
                        $quote->getShippingAddress();
                        $quote->collectTotals()->save();

                        echo json_encode($responce);
                        break;
                    }
            }
        } catch (Exception $e) {
            $responce['error'] = true;
            $responce['code'] = $e->getCode();
            $responce['message'] = $e->getMessage();
            echo json_encode($responce);
        }
    }
}


/**
 * Class M1_Bridge_Action_Clearcache
 */
class M1_Bridge_Action_Clearcache
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        switch($bridge->config->cartType) {
            case "Cubecart":
                $this->_CubecartClearCache();
                break;
            case "Prestashop11":
                $this->_PrestashopClearCache();
                break;
            case "Interspire":
                $this->_InterspireClearCache();
                break;
            case "Opencart14" :
                $this->_OpencartClearCache();
                break;
            case "XtcommerceVeyton" :
                $this->_Xtcommerce4ClearCache();
                break;
            case "Ubercart" :
                $this->_ubercartClearCache();
                break;
            case "Tomatocart" :
                $this->_tomatocartClearCache();
                break;
            case "Virtuemart113" :
                $this->_virtuemartClearCache();
                break;
            case "Magento1212" :
                $this->_magentoClearCache($bridge);
                break;
            case "Oscommerce3":
                $this->_Oscommerce3ClearCache();
                break;
            case "Oxid":
                $this->_OxidClearCache();
                break;
            case "XCart":
                $this->_XcartClearCache();
                break;
            case "Cscart203":
                $this->_CscartClearCache();
                break;
            case "Prestashop15":
                $this->_Prestashop15ClearCache();
                break;
            case "Gambio":
                $this->_GambioClearCache();
                break;
            case "Shopware":
                $this->_Shopware6ClearCashe($bridge);
                break;
        }
    }

    /**
     * @param array  $dirs
     * @param string $fileExclude - name file in format pregmatch
     * @return bool
     */
    private function _removeGarbage($dirs = array(), $fileExclude = '')
    {
        $result = true;

        foreach ($dirs as $dir) {

            if (!file_exists($dir)) {
                continue;
            }

            $dirHandle = opendir($dir);

            while (false !== ($file = readdir($dirHandle))) {
                if ($file == "." || $file == "..") {
                    continue;
                }

                if ((trim($fileExclude) != '') && preg_match("/^" .$fileExclude . "?$/", $file)) {
                    continue;
                }

                if (is_dir($dir . $file)) {
                    continue;
                }

                if (!unlink($dir . $file)) {
                    $result = false;
                }
            }

            closedir($dirHandle);
        }

        if ($result) {
            echo 'OK';
        } else {
            echo 'ERROR';
        }

        return $result;
    }

    private function _magentoClearCache(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => false);
        switch ($bridge->config->cartType) {
            case 'Magento1212':

                try {
                    $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);

                    if (version_compare($version, '2.0.0', '>=')) {
                        $_SERVER['REQUEST_URI'] = '';
                        require M1_STORE_ROOT_DIR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

                        $bootstrap = Magento\Framework\App\Bootstrap::create(rtrim(M1_STORE_ROOT_DIR, DIRECTORY_SEPARATOR), $_SERVER);
                        $objectManager = $bootstrap->getObjectManager();

                        if (isset($_POST['product_id'])) {
                            $context = $objectManager->get('\Magento\Framework\Indexer\CacheContext');
                            $eventManager = $objectManager->get('\Magento\Framework\Event\ManagerInterface');
                            $context->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, array($_POST['product_id']));
                            $eventManager->dispatch('clean_cache_by_tags', array('object' => $context));

                        } elseif (isset($_POST['cache_type'])) {

                            $cacheTypeList = $objectManager->create('Magento\Framework\App\Cache\TypeListInterface');
                            $cacheFrontendPool = $objectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
                            $types = is_array($_POST['cache_type']) ? $_POST['cache_type'] : array($_POST['cache_type']);

                            foreach ($types as $type) {
                                $cacheTypeList->cleanType($type);
                            }

                            foreach ($cacheFrontendPool as $cacheFrontend) {
                                $cacheFrontend->getBackend()->clean();
                            }
                        }
                    } else {

                        if (isset($_POST['product_id'])) {
                            //todo clear cache per product

                        } elseif (isset($_POST['cache_type'])) {

                            require_once M1_STORE_ROOT_DIR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
                            Mage::app('admin');
                            umask(0);

                            $types = is_array($_POST['cache_type']) ? $_POST['cache_type'] : array($_POST['cache_type']);
                            foreach ($types as $type) {
                                Mage::app()->getCacheInstance()->cleanType($type);
                                Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
                            }
                        }
                    }

                    $response['data'] = true;

                } catch (Exception $e) {
                    $response['error']['message'] = $e->getMessage();
                    $response['error']['code'] = $e->getCode();
                }

                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }

    private function _InterspireClearCache()
    {
        $res = true;
        $file = M1_STORE_BASE_DIR . 'cache' . DIRECTORY_SEPARATOR . 'datastore' . DIRECTORY_SEPARATOR . 'RootCategories.php';
        if (file_exists($file)) {
            if (!unlink($file)) {
                $res = false;
            }
        }
        if ($res === true) {
            echo "OK";
        } else {
            echo "ERROR";
        }
    }

    private function _CubecartClearCache()
    {
        $ok = true;

        if (file_exists(M1_STORE_BASE_DIR . 'cache')) {
            $dirHandle = opendir(M1_STORE_BASE_DIR . 'cache/');

            while (false !== ($file = readdir($dirHandle))) {
                if ($file != "." && $file != ".." && !preg_match("/^index\.html?$/", $file) && !preg_match("/^\.htaccess?$/", $file)) {
                    if (is_file( M1_STORE_BASE_DIR . 'cache/' . $file)) {
                        if (!unlink(M1_STORE_BASE_DIR . 'cache/' . $file)) {
                            $ok = false;
                        }
                    }
                }
            }

            closedir($dirHandle);
        }

        if (file_exists(M1_STORE_BASE_DIR.'includes/extra/admin_cat_cache.txt')) {
            unlink(M1_STORE_BASE_DIR.'includes/extra/admin_cat_cache.txt');
        }

        if ($ok) {
            echo 'OK';
        } else {
            echo 'ERROR';
        }
    }

    private function _PrestashopClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'tools/smarty/compile/',
            M1_STORE_BASE_DIR . 'tools/smarty/cache/',
            M1_STORE_BASE_DIR . 'img/tmp/'
        );

        $this->_removeGarbage($dirs, 'index\.php');
    }

    private function _OpencartClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'system/cache/',
        );

        $this->_removeGarbage($dirs, 'index\.html');
    }

    private function _Xtcommerce4ClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'cache/',
        );

        $this->_removeGarbage($dirs, 'index\.html');
    }

    private function _ubercartClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'sites/default/files/imagecache/product/',
            M1_STORE_BASE_DIR . 'sites/default/files/imagecache/product_full/',
            M1_STORE_BASE_DIR . 'sites/default/files/imagecache/product_list/',
            M1_STORE_BASE_DIR . 'sites/default/files/imagecache/uc_category/',
            M1_STORE_BASE_DIR . 'sites/default/files/imagecache/uc_thumbnail/',
        );

        $this->_removeGarbage($dirs);
    }

    private function _tomatocartClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'includes/work/',
        );

        $this->_removeGarbage($dirs, '\.htaccess');
    }

    /**
     * Try chage permissions actually :)
     */
    private function _virtuemartClearCache()
    {
        $pathToImages = 'components/com_virtuemart/shop_image';

        $dirParts = explode("/", $pathToImages);
        $path = M1_STORE_BASE_DIR;
        foreach ($dirParts as $item) {
            if ($item == '') {
                continue;
            }

            $path .= $item . DIRECTORY_SEPARATOR;
        }
    }

    private function _Oscommerce3ClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'osCommerce/OM/Work/Cache/',
        );

        $this->_removeGarbage($dirs, '\.htaccess');
    }

    private function _GambioClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'cache/',
        );

        $this->_removeGarbage($dirs, 'index\.html');
    }

    private function _OxidClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'tmp/',
        );

        $this->_removeGarbage($dirs, '\.htaccess');
    }

    private function _XcartClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'var/cache/',
        );

        $this->_removeGarbage($dirs, '\.htaccess');
    }

    private function _CscartClearCache()
    {
        $dir = M1_STORE_BASE_DIR . 'var/cache/';
        $res = $this->removeDirRec($dir);

        if ($res) {
            echo 'OK';
        } else {
            echo 'ERROR';
        }
    }

    private function _Prestashop15ClearCache()
    {
        $dirs = array(
            M1_STORE_BASE_DIR . 'cache/smarty/compile/',
            M1_STORE_BASE_DIR . 'cache/smarty/cache/',
            M1_STORE_BASE_DIR . 'img/tmp/'
        );

        $this->_removeGarbage($dirs, 'index\.php');
    }

    private function _Shopware6ClearCashe(M1_Bridge $bridge)
    {
        $shopwareVersion = $bridge->config->cartVars['dbVersion'];
        $response = array('error' => null, 'data' => false);

        if (version_compare($shopwareVersion, '6.0.0.0', '>=')) {
            try {
                if (file_exists(M1_STORE_BASE_DIR . '.env')) {
                    (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '.env');
                    $storeRoot = M1_STORE_BASE_DIR;
                } elseif(file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env')) {
                    (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env');
                    $storeRoot = M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR;
                } else {
                    throw new \Exception('File \'.env\' not found');
                }

                $adapter = new M1_Config_Adapter_Shopware();

                require_once $storeRoot . 'vendor/autoload.php';

                if (isset($_POST['cache_type']['user_id'])) {
                    $userId = $_POST['cache_type']['user_id'];
                } else {
                    $response['error']['message'] = 'UserId not defined';
                    $response['error']['code']    = 1;
                }

                $data = ['payload' => [], 'method' => 'DELETE', 'entity' => '_action/cache', 'meta' => ['user_id' => $userId]];
                $response['data'] = $adapter->apiSend($data);
            } catch (\Exception $e) {
                $response['error']['message'] = $e->getMessage();
                $response['error']['code'] = $e->getCode();
            }
        }

        echo json_encode($response);
    }

    /**
     * @param $dir
     * @return bool
     */
    private function removeDirRec($dir)
    {
        $result = true;

        if ($objs = glob($dir."/*")) {
            foreach ($objs as $obj) {
                if (is_dir($obj)) {
                    //print "IS DIR! START RECURSIVE FUNCTION.\n";
                    $this->removeDirRec($obj);
                } else {
                    if (!unlink($obj)) {
                        //print "!UNLINK FILE: ".$obj."\n";
                        $result = false;
                    }
                }
            }
        }
        if (!rmdir($dir)) {
            //print "ERROR REMOVE DIR: ".$dir."\n";
            $result = false;
        }

        return $result;
    }
}

/**
 * Class M1_Bridge_Action_Batchsavefile
 */
class M1_Bridge_Action_Batchsavefile extends M1_Bridge_Action_Savefile
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        $result = array();

        foreach ($_POST['files'] as $fileInfo) {
            $result[$fileInfo['id']] = $this->_saveFile(
                $fileInfo['source'],
                $fileInfo['target'],
                (int)$fileInfo['width'],
                (int)$fileInfo['height']
            );
        }

        echo serialize($result);
    }

}

/**
 * Class M1_Bridge_Action_Basedirfs
 */
class M1_Bridge_Action_Basedirfs
{

    /**
     * @param M1_Bridge $bridge
     */
    public function perform(M1_Bridge $bridge)
    {
        echo M1_STORE_BASE_DIR;
    }
}

/**
 * Class M1_Bridge_Action_GetShippingRates
 */
class M1_Bridge_Action_GetShippingRates
{
    public function perform(M1_Bridge $bridge)
    {
        $response = array('error' => null, 'data' => null);
        switch ($bridge->config->cartType) {
            case 'Magento1212':
                $quoteId = $_POST['quote_id'];
                $version = str_replace('EE.', '', $bridge->config->cartVars['dbVersion']);
                if (version_compare($version, '2.0.0', '<')) {
                    require M1_STORE_ROOT_DIR . '/app/Mage.php';
                    Mage::app();
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                } else {
                    require M1_STORE_ROOT_DIR . '/app/bootstrap.php';
                    $bootstrap = Magento\Framework\App\Bootstrap::create(M1_STORE_ROOT_DIR, $_SERVER);
                    $objectManager = $bootstrap->getObjectManager();

                    $state = $objectManager->get('\Magento\Framework\App\State');
                    $state->setAreaCode('frontend');

                    $quoteRepo = $objectManager->create('\Magento\Quote\Model\QuoteRepository');
                    $quote = $quoteRepo->get($quoteId);
                }
                $address = $quote->getShippingAddress();
                $address->setCollectShippingRates(true);
                $rates = $address->collectShippingRates()->getGroupedAllShippingRates();
                foreach ($rates as $carrier) {
                    foreach ($carrier as $rate) {
                        $response['data'][] = $rate->getData();
                    }
                }
                break;

            default:
                $response['error']['message'] = 'Action is not supported';
        }

        echo json_encode($response);
    }
}



define('M1_BRIDGE_VERSION', '128');
define('M1_BRIDGE_DOWNLOAD_LINK', 'https://clientfiles.intelligentreach.com/global/bridge2cart/bridge.download.file');
define('M1_BRIDGE_CHECK_REQUEST_KEY_LINK', 'https://clientfiles.intelligentreach.com/global/bridge2cart/check.json');
define('M1_BRIDGE_DIRECTORY_NAME', basename(getcwd()));

show_error(0);

require_once 'config.php';

if (!defined('M1_TOKEN')) {
    die('ERROR_TOKEN_NOT_DEFINED');
}

if (strlen(M1_TOKEN) !== 32) {
    die('ERROR_TOKEN_LENGTH');
}

function show_error($status)
{
    if ($status) {
        @ini_set('display_errors', 1);
        if (substr(phpversion(), 0, 1) >= 5) {
            error_reporting(E_ALL & ~E_STRICT);
        } else {
            error_reporting(E_ALL);
        }
    } else {
        @ini_set('display_errors', 0);
        error_reporting(0);
    }
}

/**
 * @param $array
 * @return array|string|stripslashes_array
 */
function stripslashes_array($array)
{
    return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if (error_reporting() === 0) {
        return;
    }

    if (strpos($message, 'Declaration of') === 0) {
        return;
    }

    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}

set_error_handler('exceptions_error_handler');

/**
 * @return bool|mixed|string
 */
function getPHPExecutable()
{
    $paths = explode(PATH_SEPARATOR, getenv('PATH'));
    $paths[] = PHP_BINDIR;
    foreach ($paths as $path) {
        // we need this for XAMPP (Windows)
        if (isset($_SERVER["WINDIR"]) && strstr($path, 'php.exe') && file_exists($path) && is_file($path)) {
            return $path;
        } else {
            $phpExecutable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
            if (file_exists($phpExecutable) && is_file($phpExecutable)) {
                return $phpExecutable;
            }
        }
    }
    return false;
}

function swapLetters($input) {
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom  = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";

    return strtr($input, $default, $custom);
}

if (version_compare(phpversion(), '7.4', '<') && get_magic_quotes_gpc()) {
    $_COOKIE  = stripslashes_array($_COOKIE);
    $_FILES   = stripslashes_array($_FILES);
    $_GET     = stripslashes_array($_GET);
    $_POST    = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}

if (isset($_POST['store_root'])) {
    if (empty($_POST['store_root'])) {
        die('ERROR_INVALID_STORE_ROOT');
    }

    $path = preg_replace('/\\' . DIRECTORY_SEPARATOR . '+/', DIRECTORY_SEPARATOR, $_POST['store_root']);
    $absPath = realpath($path);

    if (is_link($path) && strpos(realpath(dirname(__FILE__)), $absPath) === 0) {
        //bridge is contained in store's root or subdirectories
        define("M1_STORE_BASE_DIR", $absPath . DIRECTORY_SEPARATOR);
    } elseif ($_POST['store_root'] === $absPath) {
        define("M1_STORE_BASE_DIR", $_POST['store_root'] . DIRECTORY_SEPARATOR);
    } else {
        die('ERROR_INVALID_STORE_ROOT');
    }
} else {
    define("M1_STORE_BASE_DIR", realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
}

$adapter = new M1_Config_Adapter();
$bridge = new M1_Bridge($adapter->create());

if (!$bridge->getLink()) {
    die ('ERROR_BRIDGE_CANT_CONNECT_DB');
}

$bridge->run();
?>
