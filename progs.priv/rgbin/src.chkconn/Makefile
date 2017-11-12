# Makefile for chkconn.
all: chkconn
objects: chkconn.o

chkconn: Makefile chkconn.o
	#$(Q)$(CC) -c -I../../../include ../../../comlib/mem_helper.c -o mem_helper.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/libxmldbc.c -o libxmldbc.o
	#$(Q)$(CC) -o chkconn chkconn.o mem_helper.o libxmldbc.o
	$(Q)$(CC) -o chkconn chkconn.o libxmldbc.o

chkconn.o: Makefile chkconn.c
	$(Q)$(CC) -c $(CFLAGS) -I../../../include chkconn.c -o chkconn.o

clean:
	$(Q)rm -f *.o *.gdb *.elf chkconn

.PHONY: all objects clean
