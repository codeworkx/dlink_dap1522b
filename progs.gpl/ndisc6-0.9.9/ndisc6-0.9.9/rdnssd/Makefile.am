# Makefile.am - rdnssd/ directory Makefile for ndisc6
# $Id: Makefile.am 607 2008-03-22 21:55:24Z remi $

# Copyright © 2006-2007 Rémi Denis-Courmont
# This file is distributed under the same license as the ndisc6 package.

AM_CPPFLAGS = -I$(top_srcdir) -I$(top_srcdir)/include \
	-DLOCALEDIR=\"$(localedir)\" \
	-DSYSCONFDIR=\"$(sysconfdir)\" \
	-DLOCALSTATEDIR=\"$(localstatedir)\"
confdir = $(sysconfdir)/rdnssd
myrundir = $(localstatedir)/run/rdnssd

subst_script = sed \
	-e 's,[@]PERL[@],$(PERL),g' \
	-e 's,[@]PACKAGE[@],$(PACKAGE),g' \
	-e 's,[@]VERSION[@],$(VERSION),g' \
	-e 's,[@]LOCALSTATEDIR[@],$(localstatedir),g' \
	-e 's,[@]SYSCONFDIR[@],$(sysconfdir),g'

EXTRA_DIST = merge-hook.in
CLEANFILES = $(conf_SCRIPTS)
AM_INSTALLCHECK_STD_OPTIONS_EXEMPT = merge-hook
sbin_PROGRAMS = rdnssd
conf_SCRIPTS = merge-hook

# rdnssd
rdnssd_SOURCES = rdnssd.c rdnssd.h \
	icmp.c \
	netlink.c
rdnssd_LDADD = $(LIBRT) \
		@top_builddir@/compat/libcompat.a

# merge-hook
merge-hook: merge-hook.in Makefile.in $(top_builddir)/config.status
	rm -f -- $@.tmp $@
	$(subst_script) < $(srcdir)/$@.in > $@.tmp
	chmod -- +x $@.tmp
	mv -f -- $@.tmp $@

install-data-hook:
	$(install_sh) -d -- $(DESTDIR)$(myrundir)
	-chown -- nobody $(DESTDIR)$(myrundir)

uninstall-hook:
	-rmdir $(DESTDIR)$(myrundir)
