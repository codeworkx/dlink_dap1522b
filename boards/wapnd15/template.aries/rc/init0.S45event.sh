#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
event STATUS.CRITICAL add "usockc /var/gpio_ctrl PS_LED_DOING;usockc /var/gpio_ctrl WPS_NONE;"
event STATUS.READY add "usockc /var/gpio_ctrl PS_LED_DONE"
event BRIDGE.LED.ON add "usockc /var/gpio_ctrl BRIDGE_LED_ON"
event BRIDGE.LED.OFF add "usockc /var/gpio_ctrl BRIDGE_LED_OFF"

event WPS.INPROGRESS add "usockc /var/gpio_ctrl WPS_IN_PROGRESS"
event WPS.SUCCESS	 add "usockc /var/gpio_ctrl WPS_SUCCESS"
event WPS.OVERLAP	 add "usockc /var/gpio_ctrl WPS_OVERLAP"
event WPS.ERROR		 add "usockc /var/gpio_ctrl WPS_ERROR"
event WPS.NONE		 add "usockc /var/gpio_ctrl WPS_NONE"

event CHECKFW            add "/etc/events/checkfw.sh"
fi
