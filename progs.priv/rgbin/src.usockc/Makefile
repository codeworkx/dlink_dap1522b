#
#

all: usockc
objects: usockc.o

usockc: Makefile usockc.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/unixsock.c -o unixsock.o
	$(Q)$(CC) -o usockc usockc.o unixsock.o

usockc.o: Makefile usockc.c
	$(Q)$(CC) -c $(CFLAGS) -I../../../include usockc.c -o usockc.o

clean:
	$(Q)rm -f *.o *.gdb *.elf usockc

.PHONY: all objects clean
