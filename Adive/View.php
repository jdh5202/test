<?php
namespace Adive;

class View
{
    /**
     * Data available to the view templates
     * @var \Adive\Security\Set
     */
    protected $data;

    /**
     * Path to templates base directory (without trailing slash)
     * @var string
     */
    protected $templatesDirectory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = new \Adive\Security\Set();
    }

    /********************************************************************************
     * Data methods
     *******************************************************************************/

    /**
     * Does view data have value with key?
     * @param  string  $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->data->has($key);
    }

    /**
     * Return view data value with key
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data->get($key);
    }

    /**
     * Set view data value with key
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data->set($key, $value);
    }

    /**
     * Set view data value as Closure with key
     * @param string $key
     * @param mixed $value
     */
    public function keep($key, Closure $value)
    {
        $this->data->keep($key, $value);
    }

    /**
     * Return view data
     * @return array
     */
    public function all()
    {
        return $this->data->all();
    }

    /**
     * Replace view data
     * @param  array  $data
     */
    public function replace(array $data)
    {
        $this->data->replace($data);
    }

    /**
     * Clear view data
     */
    public function clear()
    {
        $this->data->clear();
    }

    /********************************************************************************
     * Legacy data methods
     *******************************************************************************/

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Get data from view
     */
    public function getData($key = null)
    {
        if (!is_null($key)) {
            return isset($this->data[$key]) ? $this->data[$key] : null;
        } else {
            return $this->data->all();
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Set data for view
     */
    public function setData()
    {
        $args = func_get_args();
        if (count($args) === 1 && is_array($args[0])) {
            $this->data->replace($args[0]);
        } elseif (count($args) === 2) {
            // Ensure original behavior is maintained. DO NOT invoke stored Closures.
            if (is_object($args[1]) && method_exists($args[1], '__invoke')) {
                $this->data->set($args[0], $this->data->protect($args[1]));
            } else {
                $this->data->set($args[0], $args[1]);
            }
        } else {
            throw new \InvalidArgumentException('Cannot set View data with provided arguments. Usage: `View::setData( $key, $value );` or `View::setData([ key => value, ... ]);`');
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Append data to view
     * @param  array $data
     */
    public function appendData($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Cannot append view data. Expected array argument.');
        }
        $this->data->replace($data);
    }

    /********************************************************************************
     * Resolve template paths
     *******************************************************************************/

    /**
     * Set the base directory that contains view templates
     * @param   string $directory
     * @throws  \InvalidArgumentException If directory is not a directory
     */
    public function setTemplatesDirectory($directory)
    {
        $this->templatesDirectory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    /**
     * Get templates base directory
     * @return string
     */
    public function getTemplatesDirectory()
    {
        return $this->templatesDirectory;
    }

    /**
     * Get fully qualified path to template file using templates base directory
     * @param  string $file The template file pathname relative to templates base directory
     * @return string
     */
    public function getTemplatePathname($file)
    {
        return $this->templatesDirectory . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
    }

    /********************************************************************************
     * Rendering
     *******************************************************************************/

    /**
     * Display template
     *
     * This method echoes the rendered template to the current output buffer
     *
     * @param  string   $template   Pathname of template file relative to templates directory
     * @param  array    $data       Any additonal data to be passed to the template.
     */
    public function display($template, $data = null)
    {
        echo $this->fetch($template, $data);
    }

    /**
     * Return the contents of a rendered template file
     *
     * @param    string $template   The template pathname, relative to the template base directory
     * @param    array  $data       Any additonal data to be passed to the template.
     * @return string               The rendered template
     */
    public function fetch($template, $data = null)
    {
        return $this->render($template, $data);
    }

    /**
     * Render a template file
     *
     * NOTE: This method should be overridden by custom view subclasses
     *
     * @param  string $template     The template pathname, relative to the template base directory
     * @param  array  $data         Any additonal data to be passed to the template.
     * @return string               The rendered template
     * @throws \RuntimeException    If resolved template pathname is not a valid file
     */
    protected function render($template, $data = null)
    {
        $templateEX=explode(':',$template); 
        $template=explode(':',$template); 
        
        if($templateEX[0]=='Default'){
            $template[0] = 'index.php';
        } else {
            if(!file_exists('Views/'.$templateEX[0].'.php')){
            $template[0] = 'index.php';
            } else {
            $template[0] = $template[0].'.php';   
            }
        }
        
        $templatePathname = $this->getTemplatePathname($template[0]);
        if (!is_file($templatePathname)) {
            throw new \RuntimeException("View cannot render `$template[0]` because the template does not exist");
        }

        $data = array_merge($this->data->all(), (array) $data);
        extract($data);
        ob_start();
        $_SESSION['path.now'] = $templateEX[0].'/'.$template[1];
        if($templateEX[0]=='Adive/Internal/Views' and $templateEX[1]!='configuration'){
            if($templateEX[1]=='login'){
                require 'Adive/Internal/login.php';
            } else {
                require 'Adive/Internal/dashboard.php';
            }
        } else {
            require $templatePathname;
        }

        return ob_get_clean();
    }
}
