<?php
$flash = $this->flashMessenger();
$flash->setMessageOpenFormat('<div%s>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
        &times;
    </button>
    <ul><li>')
    ->setMessageSeparatorString('</li><li>')
    ->setMessageCloseString('</li></ul></div>');

echo $flash->renderCurrent('error',   array('alert', 'alert-dismissible', 'alert-danger'));
echo $flash->renderCurrent('info',    array('alert', 'alert-dismissible', 'alert-info'));
echo $flash->renderCurrent('default', array('alert', 'alert-dismissible', 'alert-warning'));
echo $flash->renderCurrent('success', array('alert', 'alert-dismissible', 'alert-success'));

echo $flash->render('error',   array('alert', 'alert-dismissible', 'alert-danger'));
echo $flash->render('info',    array('alert', 'alert-dismissible', 'alert-info'));
echo $flash->render('default', array('alert', 'alert-dismissible', 'alert-warning'));
echo $flash->render('success', array('alert', 'alert-dismissible', 'alert-success'));