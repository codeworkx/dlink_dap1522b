# Makefile for usb3gkit.
all: usbreset
objects: usbreset

usbreset: Makefile usbreset
	$(Q)$(CC) -o usbreset usbreset.o

usbreset.o: Makefile usbreset.c
	$(Q)$(CC) -c $(CFLAGS) -I../../../include usbreset.c -o usbreset.o

clean:
	$(Q)rm -f *.o *.gdb *.elf usbreset

.PHONY: all objects clean
