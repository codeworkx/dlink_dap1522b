
all: tcprequest
objects: tcprequest.o

tcprequest: Makefile tcprequest.o
	$(Q)$(CC) -o tcprequest tcprequest.o

tcprequest.o: Makefile tcprequest.c
	$(Q)$(CC) -c $(CFLAGS) tcprequest.c

clean:
	$(Q)rm -f *.o *~ *.gdb *.elf tcprequest

.PHONY: all objects clean
