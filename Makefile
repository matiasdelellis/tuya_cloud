# Building

all: dev

# L10N Rules

l10n-update-pot:
	php translationtool.phar create-pot-files

l10n-pot-apply:
	php translationtool.phar convert-po-files


# Building

prepare:
	npm i

dev:
	npm run dev