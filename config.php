<?php

require_once INCLUDE_DIR . 'class.plugin.php';

class TelegramPluginConfig extends PluginConfig {
function translate() {
if (!method_exists('Plugin', 'translate')) {
return array(
function ($x) {
return $x;
},
function ($x, $y, $n) {
return $n != 1 ? $y : $x;
}
);
}
return Plugin::translate('telegram');
}

    function getOptions() {
list ($__, $_N) = self::translate();

        return array(
            'telegram' => new SectionBreakField(array(
                'hint' => $__('Telegram Bot'),
            )),
            'tgURL' => new TextboxField(array(
                'label' => 'Telegram Bot URL',
                'configuration' => array('size'=>100, 'length'=>200),
            )),
            'tgChatId' => new TextboxField(array(
                'label' => 'Chat ID',
                'configuration' => array('size'=>100, 'length'=>200),
            )),
            'tgIncludeBody' => new BooleanField(array(
                'label' => 'Include Body',
                'default' => 0,
            )),
                'tgDebug' => new BooleanField(array(
                'label' => 'Debug message in error.log',
                'default' => 0,
            ))
        );
    }
}
