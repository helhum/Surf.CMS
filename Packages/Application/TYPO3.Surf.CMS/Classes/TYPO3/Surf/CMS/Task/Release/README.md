# Details of the Release Script
## Used Repositories
* CORE https://git.typo3.org/Packages/TYPO3.CMS.git
* BASE  https://git.typo3.org/TYPO3CMS/Distributions/Base.git

## User Input
* Target Version? (major, minor, bugfix, snapshot)
* Core Branch? (master, TYPO3_7, TYPO3_6-2)
* Publish to SourceForge?

## Tasks
### On CORE
1. git clone
1. modify code and update the version in SystemEnvironmentBuilder.php
1. git commit -a
1. git tag
1. git push (+ gerrit)
### On BASE
1. git clone
1. composer install / update
1. Create ChangeLog (from CORE git log) and Symlinks
1. git push

## Publish
* Create .tar.gz and .zip
* Generate MD5/SHA1 - publish this e.g. via SCP to git.typo3.org
* Publish to SourceForge

## Meta
* Cleanup the branches / remove directories
* Create a wiki page

## Things after the release script
* Update downloads page
* Publish a news
