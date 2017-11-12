
all: sendtrapbin
objects: sendtrapbin.o

sendtrapbin: Makefile sendtrapbin.o
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -o sendtrapbin lrgbin.o

sendtrapbin.o: Makefile sendtrapbin.c
	$(Q)$(CC) -c $(CFLAGS) -I.. sendtrapbin.c

clean:
	$(Q)rm -f *.o *.gdb *.elf sendtrapbin

.PHONY: all objects clean
