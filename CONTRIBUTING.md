#Contribute To Clicky for WordPress

Community made patches, localisations, bug reports and contributions are very welcome and help make Clicky for WordPress the #1 Clicky.com plugin for WordPress.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

__Please Note:__ GitHub is for bug reports and contributions only - if you have a support question or a request for a customisation don't post here, go to our [Support Forum](http://wordpress.org/support/plugin/clicky) instead.

For localization, please refer to [translate.yoast.com](http://translate.yoast.com/projects/clicky-wordpress-plugin), though bugs with strings that can't be translated are welcome here.

## Getting Started

* Submit a ticket for your issue, assuming one does not already exist.
  * Raise it on our [Issue Tracker](https://github.com/Yoast/clicky/issues)
  * Clearly describe the issue including steps to reproduce the bug.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub.
* Run `composer install`, then `yarn install` and `grunt build` to get a working plugin.
* Make the changes to your forked repository.
  * Ensure you stick to the [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards) and have properly documented any new functions.
  * Running `grunt check` to check for coding standards issues.
* When committing, reference your issue (if present) and include a note about the fix.
* Push the changes to your fork and submit a pull request to the 'main' branch of the Clicky for WordPress repository.

## Code Documentation

* We ensure that every Clicky for WordPress function is documented well and follows the standards set by phpDoc.
* An example function can be found [here](https://gist.github.com/jdevalk/5574677).
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
* Finally, please use tabs and not spaces. The tab indent size should be 8 for all Clicky for WordPress code.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

# Additional Resources
* [General GitHub Documentation](http://help.github.com/)
* [GitHub Pull Request documentation](http://help.github.com/send-pull-requests/)