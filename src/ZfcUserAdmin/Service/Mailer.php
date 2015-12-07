<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ZfcUserAdmin\Service;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Mail;

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
        
        $r = $this->getServiceLocator()->get('Request');
        
        $config = $this->getServiceLocator()->get('config');

        if (isset($config['zfcuseradmin']['confirmation'])){
            $sender = $config['zfcuseradmin']['confirmation'];
            $mail = new Mail\Message();

            $translator = $this->getServiceLocator()->get('translator');
            $request = $this->getServiceLocator()->get('Request');
            $uri = $request->getUri();
            $scheme = $uri->getScheme();
            $host = $uri->getHost();

            
            $mail->setFrom($sender['from_email'], $sender['from_name']);
            $mail->addTo($user->getEmail(), $user->getDisplayName());
            
            $translation = $translator->translate('zfcuseradmin.confirmation_mail.subject%s');
            $subject = sprintf($translation, $host);
            $mail->setSubject($subject);
            
            if (isset($config['zfcuseradmin']['confirmation']['salt'])){
                $user->setSalt($config['zfcuseradmin']['confirmation']['salt']);
            }
            $confirmationKey = $user->obtainConfirmationKey();
            $viewHelperManager = $this->getServiceLocator()->get('viewHelperManager');
            $urlHelper = $viewHelperManager->get('url');
            $confirmationUrl = "$scheme://$host" . $urlHelper('zfcuseradmin-confirmation-check', 
                    array('id'=>$user->getId(), 'key'=>$confirmationKey)
            );

            $translation = $translator->translate('zfcuseradmin.confirmation_mail.body_txt%s%s');
            $bodyText = sprintf($translation, $host, $confirmationUrl);
            $mail->setBody($bodyText);

            $transport = new Mail\Transport\Sendmail();
            $transport->send($mail);
        }

    }
}
