<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Service;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Description of Mailer
 *
 * @author eugene
 */
class Mailer implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    
    function sendConfirmationMail($user)
    {
        $config = $this->getServiceLocator()->get('config');

        if (isset($config['zfcuseradmin']['confirmation'])){
            $sender = $config['zfcuseradmin']['confirmation'];
            
            $mail = new PHPMailer;
            $mail->isHTML(true);
            $mail->setFrom($sender['from_email'], $sender['from_name']);
            $mail->addAddress($user->getEmail(), $user->getDisplayName());

            $translator = $this->getServiceLocator()->get('translator');
            $request = $this->getServiceLocator()->get('Request');
            $uri = $request->getUri();
            $host = $uri->getHost();

            
            $translation = $translator->translate('zfcuseradmin.confirmation_mail.subject%s');
            $mail->Subject = sprintf($translation, $host);
            
            if (isset($config['zfcuseradmin']['confirmation']['salt'])){
                $user->setSalt($config['zfcuseradmin']['confirmation']['salt']);
            }
            $confirmationKey = $user->obtainConfirmationKey();
            $viewHelperManager = $this->getServiceLocator()->get('viewHelperManager');
            $urlHelper = $viewHelperManager->get('url');
            $confirmationUrl = $urlHelper('zfcuseradmin-confirmation-check', 
                    array('id'=>$user->getId(), 'key'=>$confirmationKey),
                    array('force_canonical' => true)
            );

            $translation = $translator->translate('zfcuseradmin.confirmation_mail.body_text%s%s');
            $mail->AltBody = sprintf($translation, $host, $confirmationUrl);
            
            $translation = $translator->translate('zfcuseradmin.confirmation_mail.body_html%s%s');
            $mail->Body = sprintf($translation, $host, $confirmationUrl);
            $mail->send();
        }

    }
    
}
