# Tuya Cloud
Tuya Cloud Integration for Nextcloud. This integrates ¿all? Powered by Tuya devices you have added to the Tuya Smart and Tuya Smart Life apps.

## :rocket: Installation

Tuya Cloud is available in the Nextcloud App Store and can be installed
directly from your Nextcloud installation looking in the category integration.

Nextcloud will notify you about possible updates. Please have a look at
[CHANGELOG.md](CHANGELOG.md) for details about changes.

## :exclamation: Bugs

Before reporting bugs:

* Get the newest version of the Tuya Cloud app
* Please consider also installing the [latest development version](https://github.com/matiasdelellis/tuya_cloud.git)
* [Check if they have already been reported](https://github.com/matiasdelellis/tuya_cloud/issues)

## Building the app

1. Clone this into your `apps` folder of your Nextcloud: `git clone https://github.com/matiasdelellis/tuya_cloud.git`
2. In a terminal, just run the command `make` to install the dependencies and build the aplication.
3. Enable the app through the app management of your Nextcloud.

## Notes 🤔
1. This application is enough for my current needs.
2. I am using an old API, which is not documented, and is only maintained for compatibility for older versions of Home Assistant.
3. That being said, there may be limitations that will probably force me to update the API, however I still don't expect to make that effort.
4. Beyond that, I chose this API because despite the advantages of the new API, I do not agree with its implementation. I think it is better for each person to use their credentials
