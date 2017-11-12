#

all: susockc
objects: susockc.o

susockc: Makefile susockc.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/susock.c -o susock.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/mem_helper.c -o mem_helper.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/xstream.c -o xstream.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/strobj.c -o strobj.o
	$(Q)$(CC) -o susockc susockc.o susock.o mem_helper.o xstream.o strobj.o

susockc.o: Makefile susockc.c
	$(Q)$(CC) -c $(CFLAGS) -I../../../include susockc.c -o susockc.o

clean:
	$(Q)rm -f *.o *.gdb *.elf susockc

.PHONY: all objects clean
