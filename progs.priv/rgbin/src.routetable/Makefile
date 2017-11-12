
target:=routetable

all: $(target)

objects: $(target).o

$(target): Makefile $(target).c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. $(target).c
	$(Q)$(CC) -o $(target) $(target).o lrgbin.o

$(target).i386: Makefile $(target).c
	$(Q)gcc -c -I.. ../lrgbin.c
	$(Q)gcc -c -I.. $(target).c
	$(Q)gcc -o $(target) $(target).o lrgbin.o

$(target).o: Makefile $(target).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(target).c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf $(target)

.PHONY: all objects clean
