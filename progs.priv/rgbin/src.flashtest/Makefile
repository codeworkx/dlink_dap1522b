
all: flashtest
objects: flashtest.o

flashtest: Makefile flashtest.o
	$(Q)$(CC) -o flashtest flashtest.o

flashtest.o: Makefile flashtest.c
	$(Q)$(CC) -c $(CFLAGS) flashtest.c

clean:
	$(Q)rm -f *.o rgdb *.gdb *.elf

.PHONY: all objects clean
