<?php

/**************************************************************************
/*    Class to embed and extract images to/from a mysql database uses PDO *
/*																          *
/*    Free software- GNU/GPL (C)Can Ince 17-January-2014  		          *
/*	  http://www.seifzadeh.blog.ir								          *
/*************************************************************************/

class Img2Db
{    
    // @object, The PDO object
    private $_pdo;
    
    // @array,  The database settings
    public $_config;

    /**
     * 
     * <b>
     * initialazion of config and connect the database
     * 
     */
    function __construct() {
        
        // init the config
        $this->init();
        
        // connect to database
        $this->connect();
    }
    

    /**
     * <b>
     * The base Config for init the img2db class
     * 
     * @param Array $config The base configuration
     * @see set(), Array Set the base config on $_config public variable 
     * 
     * <p>
     * Defult config set for sample Database. you change the new database for needs.
     */
    public function init($config = array()) {
        if (!empty($config)) {
            $this->_config = $config;
        } else {
            $this->_config = array(
            
            // Database Host
            'host' => in_array('host', $config) ? $config['host'] : 'localhost',
            
            // Database Username
            'username' => in_array('username', $config) ? $config['username'] : 'root',
            
            // Database Password
            'password' => in_array('password', $config) ? $config['password'] : '123',
            
            // Database Name
            'database' => in_array('database', $config) ? $config['database'] : 'img2db',
            
            // Table For Save Image
            'table' => in_array('table', $config) ? $config['table'] : 'ImageTable',
            
            // Table (Id,Name,File) Field Name
            'field_id' => in_array('field_id', $config) ? $config['field_id'] : 'ImageId', 'field_image_name' => in_array('field_id', $config) ? $config['field_id'] : 'ImageName', 'field_image_file' => in_array('field_image_file', $config) ? $config['field_image_file'] : 'ImageFile', 'field_image_type' => in_array('field_image_type', $config) ? $config['field_image_type'] : 'ImageType',
            
            // Image Saved From Path Or Form
            // From Form : 'file_from' => 'form'; Sample: '../files/images/image1.jpg'
            // From Path : 'file_from' => 'form'; Sample: $_FILES['myimg']
            'file_from' => in_array('file_from', $config) ? $config['file_from'] : 'form',
            
            // if the saved from form set the form input file name
            // Sample : <input type="file" name="myFileName"> == input_name=>'myFileName'
            'input_name' => in_array('input_name', $config) ? $config['input_name'] : 'file');
        }
    }
    
    /**
     * <b>
     * Auto Connect The Mysql Database Uses Pdo
     * <p>
     * @see set(), set the connect config on $_pdo private variable
     * Connect Uses The Base Config. if the get Error set the you database Config the init <i>method.
     */
    private function connect() {
        try {
            
            // Connect The Database Uses Setting
            $this->_pdo = new PDO('mysql:host=' . $this->_config['host'] . ';dbname=' . $this->_config['database'], $this->_config['username'], $this->_config['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            
            // We can now log any exceptions on Fatal error.
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e) {
            
            // Show Error Connection
            echo 'ERROR Connection: ' . $e->getMessage();
            die();
        }
    }
    
    /**
     * <b>
     * Convert and save the image to mysql database
     * 
     * @param Array $files if the save file to database from form set the $_FILES file array <i>Example: $files = $_FILES['avatar_image']; //on the form <input type="file" name="avatar_image" />
     * @param String $path if the file saved on the server,set path of file.
     * @param String $name if the new name of the file set the filename <i>Example: $filename = 'admin_1_avatar'; // the file upload or saved path named: avatar2014.jpg
     * @return Integer $ret the saved on database returned inserted id or error in saved return null.
     * 
     * <p>
     * convert image to blob uses the <i>img2b method.
     * for the save uses the file upload or file path to saved on youre server.
     */
    public function save($files = null, $path = null, $name = null) {
        try {
            
            // File Data
            $data = null;
            $ret = false;
            $type = null;
            
            switch ($this->_config['file_from']) {

            	// The base config get file from form uploaded
                case 'form': {
                            if ($files != null && is_array($files)) {
                                
                                // Make sure the user actually
                                // selected and uploaded a file
                                if (isset($files[$this->_config['input_name']]) && $files[$this->_config['input_name']]['size'] > 0) {
                                    
                                    // Temporary file name stored on the server
                                    $tmpName = $files[$this->_config['input_name']]['tmp_name'];
                                    
                                    $type = $files[$this->_config['input_name']]['type'];
                                    
                                    // Read the file
                                    $data = fopen($tmpName, 'rb');
                                    
                                    if ($name == null) $name = $files[$this->_config['input_name']]['name'];
                                }
                            }
                        }
                        break;
                    
                    // if the save image on the server set the config file_from to path <i>(uses the init method for config) set image path for save the database
                    case 'path': {
                                if ($path != null) {
                                    $data = fopen($path, "rb");
                                    
                                    // get the file type
                                    $path_info = pathinfo($path);
                                    $type = $path_info['extension'];
                                    
                                    // get file full name
                                    if ($name == null) $name = basename($path);
                                }
                            }
                    }
                    
                    // file data (image and type) save to database
                    if ($data != null) {
                        
                        // Prepare For Insert
                        $stmt = $this->_pdo->prepare('INSERT INTO `' . $this->_config['table'] . '` (`' . $this->_config['field_image_name'] . '`,`' . $this->_config['field_image_file'] . '`,`' . $this->_config['field_image_type'] . '`) VALUES(?,?,?)');
                        
                        // bind paramater
                        $stmt->bindParam(1, $name);
                        $stmt->bindParam(2, $data, PDO::PARAM_LOB);
                        $stmt->bindParam(3, $type);
                        
                        // excute the query
                        $stmt->execute();
                        
                        // Return of Last Insert Id
                        $ret = $this->_pdo->lastInsertId();
                    }
                    
                    // if the save image to database return inserted id else returned null.
                    return $ret;
                }
                catch(PDOException $e) {
                    
                    // Show Error Query
                    echo 'ERROR Query: ' . $e->getMessage();
                    die();
                }
            }
    
    /**
     * <b> Get file from database and show
     * 
     * @param String $name name of file for query
     * @param Integer $id id for query
     * @return image data
     * 
     */
    public function show($name = null, $id = null) {
        try {
            
            // Prepare For Select
            $sth = $this->_pdo->prepare('SELECT * FROM `' . $this->_config['table'] . '` WHERE `' . $this->_config['field_id'] . '`=:ImageId OR `' . $this->_config['field_image_name'] . '`=:ImageName LIMIT 1');
            
            // Execute Of Query
            $sth->execute(array(':ImageId' => $id, ':ImageName' => $name));
            
            // Fetched Of Row
            $image = $sth->fetch(PDO::FETCH_ASSOC);
            if ($image != null) {
                header("Content-type: " . $image[$this->_config['field_image_type']]);
                return $image[$this->_config['field_image_file']];
            }
        }
        catch(PDOException $e) {
            
            // Show Error Query
            echo 'ERROR Query: ' . $e->getMessage();
            die();
        }
    }

    /**
     * <b> Convert image to saved to database
     * 
     * @param String/Array $filesOrPath if file from form set the $_FILES data array or saved on server set the file path.
     * @param Bool $full true return array of file of file type and data for saved on database or flase for return data of image.
     * @return Array/Blob array of data and type or data
     *
     */
    public function img2sql($filesOrPath, $full = true) {

    	// for blob of data file 
        $data = null;

        // saved of detected type data 
        $type = null;

        // check the file from form uploaded or saved on path
        if ($this->_config['file_from'] == 'form') {
            
            // Temporary file name stored on the server
            $tmpName = $filesOrPath[$this->_config['input_name']]['tmp_name'];
            
            // detected type from form type attribute
            $type = $filesOrPath[$this->_config['input_name']]['type'];
            
            // Read the file
            $data = fopen($tmpName, 'rb');

        // if the file from saved on server    
        } else {
            
            // read file for convert
            $data = fopen($filesOrPath, "rb");
            
            // detected file type of saved on ser
            $path_info = pathinfo($filesOrPath);
            $type = $path_info['extension'];
        }
        
        // return the array of file blob data or type
        if ($full) return array('data' => $data, 'type' => $type);

        // return blob data
        else return $data;
    }
}
?>