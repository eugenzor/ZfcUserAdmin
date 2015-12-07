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
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

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
            $confirmationUrl = $urlHelper('zfcuseradmin-confirmation-check', 
                    array('id'=>$user->getId(), 'key'=>$confirmationKey),
                    array('force_canonical' => true)
            );

            $translation = $translator->translate('zfcuseradmin.confirmation_mail.body_text%s%s');
            $bodyText = sprintf($translation, $host, $confirmationUrl);
            $bodyTextPart = new MimePart($bodyText);
            $bodyTextPart->setType('text/plain');
                    
            
            $translation = $translator->translate('zfcuseradmin.confirmation_mail.body_html%s%s');
            $bodyHtml = sprintf($translation, $host, $confirmationUrl);
            $bodyHtmlPart = new MimePart($bodyHtml);
            $bodyHtmlPart->setType('text/html');
            
            $message = new MimeMessage();
            $message->setParts(array($bodyTextPart, $bodyHtmlPart));
            
            $mail->setBody($message);

            $transport = new Mail\Transport\Sendmail();
            $transport->send($mail);
        }

    }
}
