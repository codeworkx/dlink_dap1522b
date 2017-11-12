
all: tlogs

objects: tlogs.o

tlogs: Makefile tlogs.c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. tlogs.c
	$(Q)$(CC) -o tlogs tlogs.o lrgbin.o

tlogs.i386: Makefile tlogs.c
	$(Q)gcc -c -I.. ../lrgbin.c
	$(Q)gcc -c -I.. tlogs.c
	$(Q)gcc -o tlogs tlogs.o lrgbin.o

tlogs.o: Makefile tlogs.c
	$(Q)$(CC) -c $(CFLAGS) -I.. tlogs.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf tlogs

.PHONY: all objects clean
