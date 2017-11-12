#

all: chnet

objects: chnet.o

chnet: Makefile chnet.o
	$(Q)$(CC) -c chnet.c
	$(Q)$(CC) -o chnet chnet.o

chnet.o: Makefile chnet.c
	$(Q)$(CC) -c $(CFLAGS) chnet.c

clean:
	$(Q)rm -f *.o *.gdb *.elf chnet

.PHONY: all object clean
