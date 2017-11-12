all: simunlock
objects: simunlock.o 

simunlock: Makefile simunlock.c
	$(Q)$(CC) -c simunlock.c -I../../../include
	$(Q)$(CC) -c -I../../../include ../../../comlib/libxmldbc.c -o libxmldbc.o
	$(Q)$(CC) -c -I../../../include ../../../comlib/md5.c -o md5.o
	$(Q)$(CC) -o simunlock simunlock.o libxmldbc.o md5.o

simunlock.o: Makefile simunlock.c
	$(Q)$(CC) -c $(CFLAGS) -I.. simunlock.c

clean:
	$(Q)rm -f *.o *.gdb *.elf simunlock

.PHONY: all objects clean
