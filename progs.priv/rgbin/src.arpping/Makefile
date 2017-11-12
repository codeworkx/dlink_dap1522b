

all: arpping

objects: arpping.o

arpping: Makefile arpping.c arpping.h
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. arpping.c
	$(Q)$(CC) -o arpping arpping.o lrgbin.o

arpping.i386: Makefile arpping.c arpping.h
	$(Q)cc -c -I.. ../lrgbin.c
	$(Q)cc -c -I.. arpping.c
	$(Q)cc -o arpping arpping.o lrgbin.o

arpping.o: Makefile arpping.c arpping.h
	$(Q)$(CC) -c $(CFLAGS) -I.. arpping.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf arpping

.PHONY: all objects clean
