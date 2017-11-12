/*
 * This file is subject to the terms and conditions of the GNU General Public
 * License.  See the file "COPYING" in the main directory of this archive
 * for more details.
 *
 * Copyright (C) 2007 Ralf Baechle (ralf@linux-mips.org)
 */
#include <linux/init.h>
#include <linux/serial_8250.h>
#include <asm/rt2880/surfboard.h>
#include <asm/rt2880/surfboardint.h>
#include <asm/rt2880/rt_mmap.h>


#define PORT(base, int)						\
{											\
	.iobase		= base,						\
	.mapbase	= base,						\
	.irq		= int,						\
	.uartclk	= 57600 *16,				\
	.iotype		= UPIO_PORT,				\
	.flags		= UPF_BOOT_AUTOCONF | UPF_SKIP_TEST,		\
	.regshift	= 2,						\
}

static struct plat_serial8250_port uart8250_data[] = {
	PORT(KSEG1ADDR(RALINK_UART_BASE), SURFBOARDINT_UART),
	PORT(KSEG1ADDR(RALINK_UART_LITE_BASE), SURFBOARDINT_UART1),
	{ },
};

static struct platform_device uart8250_device = {
	.name			= "serial8250",
	.id			= PLAT8250_DEV_PLATFORM,
	.dev			= {
		.platform_data	= uart8250_data,
	},
};

static int __init uart8250_init(void)
{	
	return platform_device_register(&uart8250_device);
}

device_initcall(uart8250_init);

MODULE_AUTHOR("Ralf Baechle <ralf@linux-mips.org>");
MODULE_LICENSE("GPL");
MODULE_DESCRIPTION("Generic 8250 UART probe driver");
