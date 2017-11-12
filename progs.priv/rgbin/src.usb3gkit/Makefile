# Makefile for usb3gkit.
all: usb3gkit
objects: usb3gkit.o

usb3gkit: Makefile usb3gkit.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/mem_helper.c -o mem_helper.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/libxmldbc.c -o libxmldbc.o
	$(Q)$(CC) -o usb3gkit usb3gkit.o mem_helper.o libxmldbc.o

usb3gkit.o: Makefile usb3gkit.c
	$(Q)$(CC) -c $(CFLAGS) -I../../../include usb3gkit.c -o usb3gkit.o

clean:
	$(Q)rm -f *.o *.gdb *.elf usb3gkit

.PHONY: all objects clean
