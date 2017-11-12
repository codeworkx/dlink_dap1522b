
all: memtest
objects: memtest.o

memtest: Makefile memtest.o
	$(Q)$(CC) -o memtest memtest.o

memtest.o: Makefile memtest.c
	$(Q)$(CC) -c $(CFLAGS) memtest.c

clean:
	$(Q)rm -f *.o rgdb *.gdb *.elf

.PHONY: all objects clean
