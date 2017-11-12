
all: scut
objects: scut.o

scut: Makefile scut.o
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. scut.c
	$(Q)$(CC) -o scut scut.o lrgbin.o

scut.o: Makefile scut.c
	$(Q)$(CC) -c $(CFLAGS) -I.. scut.c

clean:
	$(Q)rm -f *.o *.gdb *.elf scut

.PHONY: all objects clean
