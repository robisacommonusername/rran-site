<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */ 
namespace App\Controller;
require('Component/FileEncryptor.php');
use Cake\Controller\Controller;
use Cake\I18n\Time;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        Time::setToStringFormat('YYYY-MM-dd HH:mm:ss');
        $this->loadComponent('Csrf');
        $this->loadComponent('RequestHandler'); //for json data
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password'
                    ]
                ]
            ],
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login'
            ]
        ]);
        
        // Allow the display action so our pages controller
        // continues to work. We don't actually use the pages controller
        //so disable this for now
        //$this->Auth->allow(['display']);
    }
    
    //allow us to get the user information in the view
    public function beforeRender(\Cake\Event\Event $event){
		 $this->set(['userData'=> $this->Auth->user()]);
	}
	
	//Authorization. By default, admin can do everything, everything else
	//is forbidden
	public function isAuthorized($user){
		if ($user['is_admin']){
			return true;
		}
		return false;
	}
}
