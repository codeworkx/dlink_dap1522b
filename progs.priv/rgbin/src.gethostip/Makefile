
all: gethostip
objects: gethostip.o

gethostip: Makefile gethostip.c
	$(Q)$(CC) $(CFLAGS) -o gethostip gethostip.c

i386: Makefile gethostip.c
	$(Q)cc $(CFLAGS) -o gethostip.i386 gethostip.c

gethostip.o:
	$(Q)$(CC) $(CFLAGS) -c gethostip.c

clean:
	$(Q)rm -f gethostip gethostip.i386 *.o

.PHONY: all objects clean i386
