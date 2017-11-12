target=rstrcmp

all: $(target)

objects: $(target).o

$(target): Makefile $(target).c
	$(CC) -c -I.. ../lrgbin.c
	$(CC) -c -I.. $(target).c
	$(CC) -o $(target) $(target).o lrgbin.o

$(target).i386: Makefile $(target).c
	gcc -c -I.. ../lrgbin.c
	gcc -c -I.. $(target).c
	gcc -o $(target) $(target).o lrgbin.o

$(target).o: Makefile $(target).c
	$(CC) -c $(CFLAGS) -I.. $(target).c

clean:
	rm -f *.o *.gdb *~ *.elf $(target)

.PHONY: all objects clean
