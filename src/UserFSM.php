<?php

namespace IDM;

use Finite\Event\TransitionEvent;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\Transition\Transition;
use Symfony\Component\EventDispatcher\Event;

class UserFSM
{
    const USER_STATE_INIT = 'start';
    const USER_WAIT_FOR_CONFIRMATION = 'waitforconfirm';
    const USER_STATE_NORMAL = 'normal';
    const USER_STATE_LOCK = 'locked';
    const USER_STATE_CLOSED = 'closed';

    const TRANSITION_REGISTER = 'register';
    const TRANSITION_RESEND = 'resend';
    const TRANSITION_CONFIRM = 'confirm';
    const TRANSITION_CONFIRM_FORGOTPW = 'confirmforgotpw';
    const TRANSITION_RESETPASSWORD = 'resetpw';
    const TRANSITION_FORGOTPW = 'forgotpw';
    const TRANSITION_RESEND_FORGOTPW = 'resendforgotpw';
    const TRANSITION_MODIFY = 'modify';
    const TRANSITION_LOGIN = 'login';
    const TRANSITION_ADMINLOCK = 'adminlock';
    const TRANSITION_LOCK = 'lock';
    const TRANSITION_UNLOCK = 'unlock';
    const TRANSITION_CLOSE = 'close';

    protected $dispatcher;
    protected $sm;
    protected $userStorage;

    public function __construct(StatefulInterface $user, IIDMStorage $userStorage, $dispatcher){
        $this->dispatcher = $dispatcher;
        $this->userStorage = $userStorage;
        $this->sm = new StateMachine();
        $this->init($user);
    }

    public function userExisted(string $uuid){

    }

    public function userExisted2(string $uid, string $authSource){

        return false;
    }
    
    public function getStandardClaim(){
        $user = $this->sm->getObject();
        if($user instanceof UserEntity){
            return $user->getStandardClaims();
        }
    }

    public function getExtraClaim(){
        $user = $this->sm->getObject();
        if($user instanceof UserEntity){
            return $user->getExtraClaims();
        }
    }


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////

    protected function init(StatefulInterface $UserEntity)
    {
        $sm = new StateMachine();

        // Define states
        $initState = new State(UserFSM::USER_STATE_INIT, StateInterface::TYPE_INITIAL);
        $initState->setProperties(array(
            'actions' => 'register'
        ));

        $sm->addState($initState);

        $sm->addState(UserFSM::USER_WAIT_FOR_CONFIRMATION);

        $normalState = new State(UserFSM::USER_STATE_NORMAL, StateInterface::TYPE_NORMAL);
        $normalState->setProperties(array(
            'sub',
            'name',
            'given_name',
            'family_name',
            'middle_name',
            'nickname',
            'preferred_username',
            'profile',
            'email',
            'email_verified',
            'gender',
            'birthdate',
            'zoneinfo',
            'locale',
            'phone_number',
            'address',
            'preferred_theme',
            'preferred_lang',
        ));
        $sm->addState($normalState);

        $sm->addState(new State(UserFSM::USER_STATE_LOCK, StateInterface::TYPE_NORMAL));
        $sm->addState(new State(UserFSM::USER_STATE_CLOSED, StateInterface::TYPE_FINAL));


        // Define transitions
        $sm->addTransition(new Transition(UserFSM::TRANSITION_REGISTER,
            UserFSM::USER_STATE_INIT,
            UserFSM::USER_WAIT_FOR_CONFIRMATION,
            array($this, 'gRegister')
        ));

        // user has a limit times to do the confirmation request
        $sm->addTransition(new Transition(UserFSM::TRANSITION_RESEND,
            UserFSM::USER_WAIT_FOR_CONFIRMATION,
            UserFSM::USER_WAIT_FOR_CONFIRMATION,
            array($this, 'gReSend')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_CONFIRM,
            UserFSM::USER_WAIT_FOR_CONFIRMATION,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gConfirm')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_CONFIRM_FORGOTPW,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gConfirmForgotPw')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_LOGIN,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gLogin')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_MODIFY,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gModify')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_RESETPASSWORD,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gResetPW')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_FORGOTPW,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gForgotPW')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_RESEND_FORGOTPW,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gReSendForgotPW')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_LOCK,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_LOCK,
            array($this, 'gLock')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_ADMINLOCK,
            UserFSM::USER_STATE_NORMAL,
            UserFSM::USER_STATE_LOCK,
            array($this, 'gAdminLock')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_UNLOCK,
            UserFSM::USER_STATE_LOCK,
            UserFSM::USER_STATE_NORMAL,
            array($this, 'gUnlock')
        ));

        $sm->addTransition(new Transition(UserFSM::TRANSITION_CLOSE,
            UserFSM::USER_STATE_LOCK,
            UserFSM::USER_STATE_CLOSED,
            array($this, 'gClose')
        ));


        // Initialize
        $sm->setObject($UserEntity);
        if ($UserEntity instanceof UserEntity) {
            $sm->setDispatcher($UserEntity->getDispatcher());
        }

        $sm->initialize();

        /**
         * Add listeners
         */
        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_REGISTER,
            array($this, 'aRegister'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_RESEND,
            array($this, 'aReSend'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_CONFIRM,
            array($this, 'aConfirm'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_CONFIRM_FORGOTPW,
            array($this, 'aConfirmForgotPw'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_FORGOTPW,
            array($this, 'aForgotPW'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_RESEND_FORGOTPW,
            array($this, 'aReSendForgotPW'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_RESETPASSWORD,
            array($this, 'aResetPW'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_MODIFY,
            array($this, 'aModify'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_LOGIN,
            array($this, 'aLogin'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_ADMINLOCK,
            array($this, 'aAdminLock'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_LOCK,
            array($this, 'aLock'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_UNLOCK,
            array($this, 'aUnlock'));

        $sm->getDispatcher()->addListener('finite.post_transition.' . UserFSM::TRANSITION_CLOSE,
            array($this, 'aClose'));


        $this->sm = $sm;
    }

    protected function gRegister()
    {
        // TODO password complexity check
        return true;
    }

    protected function gReSend()
    {
        // send_confirmation_count < = max_allowed
        $user = $this->sm->getObject();
        if ($user instanceof UserEntity) {
            if ($user->getSendConfirmCount() > IConfService::MAX_REQUEST_REGISTER) return false;
        }
        return true;
    }

    protected function gConfirmForgotPw(){
        // send_confirmation_count < = max_allowed
        // Activation code should match
        $user = $this->sm->getObject();
        if ($user instanceof UserEntity) {
            $now = new \DateTime('now');
            if (($now->getTimestamp() - $user->getActivationCodeLifetime()->getTimestamp()) > IConfService::ACTIVATION_CODE_LIFE_TIME) return false;
        }

        return true;
    }

    protected function gConfirm()
    {
        // send_confirmation_count < = max_allowed
        // Activation code should match
        $user = $this->sm->getObject();
        if ($user instanceof UserEntity) {
            $now = new \DateTime('now');
            if (($now->getTimestamp() - $user->getActivationCodeLifetime()->getTimestamp()) > IConfService::ACTIVATION_CODE_LIFE_TIME) return false;
        }

        return true;
    }

    protected function gResetPW()
    {
        return true;
    }

    protected function gForgotPW()
    {
        $user = $this->sm->getObject();
        if ($user instanceof UserEntity) {
            if (
                $user->getForgetPwCount() > IConfService::MAX_REQUEST_FORGOTPW
            ) return false;
        }
        return true;
    }

    protected function gReSendForgotPW()
    {
        $user = $this->sm->getObject();
        if ($user instanceof UserEntity) {
            if (
                $user->getForgetPwCount() > IConfService::MAX_REQUEST_FORGOTPW
            ) return false;
        }
        return true;
    }

    protected function gModify()
    {
        return true;
    }

    protected function gLogin()
    {
        return true;
    }

    protected function gAdminLock()
    {
        return true;
    }

    protected function gLock()
    {
        return true;
    }

    protected function gUnlock()
    {
        return true;
    }

    protected function gClose()
    {
        return true;
    }

    /**
     * @param Event $event
     */

    protected function aRegister(Event $event)
    {
        // Generate activation code
        // Save activation code
        // Send to user via extuid

        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setCreatedAt(new \DateTime('now'));
                $UserEntity->setUpdatedAt(new \DateTime('now'));
                $UserEntity->setActivationCodeLifetime(new \DateTime('now'));
                $UserEntity->setActivationCode(UserFSM::genActivationCode());
                $UserEntity->setSendConfirmCount($UserEntity->getSendConfirmCount() + 1);
            }
        }
    }

    /**
     * @param Event $event
     */
    protected function aReSend(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setUpdatedAt(new \DateTime('now'));
                $UserEntity->setSendConfirmCount($UserEntity->getSendConfirmCount() + 1);
            }
        }
    }

    protected function aConfirm(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setUpdatedAt(new \DateTime('now'));
                $UserEntity->setSendConfirmCount(0);
            }
        }
    }

    protected function aConfirmForgotPw(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setUpdatedAt(new \DateTime('now'));
                $UserEntity->setSendConfirmCount(0);
            }
        }
    }

    protected function aResetPW(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {

            }
        }
    }

    protected function aModify(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setUpdatedAt(new \DateTime('now'));
            }
        }
    }

    protected function aLogin(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                $UserEntity->setLastlogon(new \DateTime('now'));
                $UserEntity->setLogonCount($UserEntity->getLogonCount() + 1);
                $UserEntity->setSendConfirmCount(0);
                $UserEntity->setLockTime(0);
                $UserEntity->setForgetPwCount(0);
                $UserEntity->setLoginFailedCount(0);
            }
        }
    }

    protected function aForgotPW(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                // TODO
                // Generate activation code
                // Set activation code
                // Set activation code lifetime
                // Send to client by extuid
                // Set forget_pw_count =+ 1
                $UserEntity->setActivationCode(UserFSM::genActivationCode());
                $UserEntity->setActivationCodeLifetime(new \DateTime("now"));
                $UserEntity->setForgetPwCount($UserEntity->getForgetPwCount() + 1);
            }
        }
    }

    protected function aReSendForgotPW(Event $event)
    {
        if ($event instanceof TransitionEvent) {
            $UserEntity = $event->getStateMachine()->getObject();
            if ($UserEntity instanceof UserEntity) {
                // TODO
                // Generate activation code
                // Set activation code
                // Set activation code lifetime
                // Send to client by extuid
                // Set forget_pw_count =+ 1
                $UserEntity->setActivationCode(UserFSM::genActivationCode());
                $UserEntity->setActivationCodeLifetime(new \DateTime("now"));
                $UserEntity->setForgetPwCount($UserEntity->getForgetPwCount() + 1);

                // Send mail
            }
        }
    }

    protected function aAdminLock(Event $event)
    {
        if ($event instanceof TransitionEvent) {

        }
    }

    protected function aLock(Event $event)
    {
        if ($event instanceof TransitionEvent) {

        }
    }

    protected function aUnlock(Event $event)
    {
        if ($event instanceof TransitionEvent) {

        }
    }

    protected function aClose(Event $event)
    {
        if ($event instanceof TransitionEvent) {

        }
    }

    protected function genActivationCode($length = 64)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

