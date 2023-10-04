# Makefile for building the project
app_name=tuya_cloud
project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
sign_dir=$(build_dir)/sign
appstore_dir=$(build_dir)/appstore
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates

# Building

all: dev

# Dev env management
dev-setup: clean clean-dev npm-init

npm-init:
	npm ci

# L10N Rules
l10n-update-pot:
	php translationtool.phar create-pot-files

l10n-pot-apply:
	php translationtool.phar convert-po-files

# Cleaning
clean:
	rm -rf js

clean-dev:
	rm -rf node_modules

# Building
build-js:
	npm run dev

build-js-production:
	npm run build

appstore: dev-setup build-js-production
	mkdir -p $(sign_dir)
	rsync -a \
	    --exclude='.*' \
	    --exclude=build \
	    --exclude=CONTRIBUTING.md \
	    --exclude=composer* \
	    --exclude=doc \
	    --exclude=Makefile \
	    --exclude=package*json \
	    --exclude=node_modules \
	    --exclude=js/templates \
	    --exclude=src \
	    --exclude=templates/fake.php \
	    --exclude=translation* \
	    --exclude=webpack*.js \
	    --exclude=*.js.map \
	    --exclude=psalm.xml \
	$(project_dir) $(sign_dir)
	@echo "Signing…"
	php ../../occ integrity:sign-app \
	    --privateKey=$(cert_dir)/$(app_name).key\
	    --certificate=$(cert_dir)/$(app_name).crt\
	    --path=$(sign_dir)/$(app_name)
	tar -czf $(build_dir)/$(app_name).tar.gz \
	    -C $(sign_dir) $(app_name)
	openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name).tar.gz | openssl base64
