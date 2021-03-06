<?php
/**
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/skeleton
 */

/**
 * @namespace
 */
namespace Application;

use Bluz\Application\Application;
use Bluz\Application\Exception\ForbiddenException;
use Bluz\Auth\AuthException;
use Bluz\Proxy\Layout;
use Bluz\Proxy\Logger;
use Bluz\Proxy\Messages;
use Bluz\Proxy\Response;
use Bluz\Proxy\Request;
use Bluz\Proxy\Session;
use Parse\ParseClient;

/**
 * Bootstrap
 *
 * @category Application
 * @package  Bootstrap
 *
 * @author   Anton Shevchuk
 * @created  20.07.11 17:38
 */
class Bootstrap extends Application
{
    /**
     * {@inheritdoc}
     */
    public function init($environment = 'production')
    {
        parent::init($environment);

        $options = [
            'appId' => '...',
            'restApiKey' => '...',
            'masterKey' => '...'
        ];
        ParseClient::initialize($options['appId'], $options['restApiKey'], $options['masterKey']);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return void
     */
    protected function preDispatch($module, $controller, $params = array())
    {
        // example of setup default title
        Layout::title("Bluz Skeleton");

        // apply "remember me" function
        if (!$this->user() && !empty($_COOKIE['rToken']) && !empty($_COOKIE['rId'])) {
            // try to login
            try {
                Auth\Table::getInstance()->authenticateCookie($_COOKIE['rId'], $_COOKIE['rToken']);
            } catch (AuthException $e) {
                $this->getResponse()->setCookie('rId', '', 1, '/');
                $this->getResponse()->setCookie('rToken', '', 1, '/');
            }
        }

        parent::preDispatch($module, $controller, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return void
     */
    protected function postDispatch($module, $controller, $params = array())
    {
        parent::postDispatch($module, $controller, $params);
    }

    /**
     * Denied access
     * @throws ForbiddenException
     * @return void
     */
    public function denied()
    {
        // add messages make sense only if presentation is not json, xml, etc
        if (!$this->getResponse()->getPresentation()) {
            Messages::addError('You don\'t have permissions, please sign in');
        }
        // redirect to login page
        if (!$this->user()) {
            // save URL to session and redirect make sense if presentation is null
            if (!$this->getResponse()->getPresentation()) {
                Session::set('rollback', Request::getRequestUri());
                $this->redirectTo('users', 'signin');
            }
        }
        throw new ForbiddenException();
    }

    /**
     * Render with debug headers
     * @return void
     */
    public function render()
    {
        if ($this->debugFlag && !headers_sent()) {
            $debugString = sprintf(
                "%fsec; %skb",
                microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
                ceil((memory_get_usage()/1024))
            );
            $debugString .= '; '.Request::getModule() .'/'. Request::getController();

            Response::setHeader('Bluz-Debug', $debugString);

            if ($info = Logger::get('info')) {
                Response::setHeader('Bluz-Bar', json_encode($info));
            } else {
                Response::setHeader('Bluz-Bar', '{"!":"Logger is disabled"}');
            }
        }
        parent::render();
    }

    /**
     * Finish it
     * @return void
     */
    public function finish()
    {
        if ($messages = Logger::get('error')) {
            foreach ($messages as $message) {
                errorLog($message);
            }
        }
    }
}
