
all: pfile
objects: pfile.o

pfile: Makefile pfile.c
	$(Q)$(CC) -o pfile pfile.c

pfile.o: Makefile pfile.c
	$(Q)$(CC) -c $(CFLAGS) pfile.c

clean:
	$(Q)rm -f *.o *.gdb *.elf pfile

.PHONY: all objects clean
