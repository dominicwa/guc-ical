# GUC-iCal

:exclamation: GUC-iCal is not currently working or maintained. IMDb keeps changing their data formatting breaking the scrape.

GUC-iCal is a PHP script that converts cinema release data (published by [IMDb](https://www.imdb.com/calendar)) into an iCal feed for use in calendar software. Using it, you can keep an eye on upcoming movie releases in your region.

To deploy this script yourself, you'll need to install the package dependencies using [Composer](https://getcomposer.org/download/). Then it should just run as a single script in most PHP environments.

If you're familiar with Docker and Docker Compose, you can quickly spin up a micro service of GUC-iCal using the included YAML config and Dockerfile.

You'll notice there are some configs at the top of the script for setting the default feed region and calendar timezone. These values can be overridden with both environment flags in the docker-compose.yaml and (if allowed in the script config) using querystring parameters like so:

```
https://url/to/guc-ical/?r=AU&tz=Australia/Perth
https://url/to/guc-ical/?r=GB&tz=Europe/London
```

To work out what the different region values are hover your mouse over the list at https://www.imdb.com/calendar for each country.

To work out what the different timezone values are, see [this page](https://www.php.net/manual/en/timezones.php).

## Some things to bare in mind...

- If IMDb changes the format of their data this script will probably break. I'm running it in my calendar so I should notice if that happens and will do my best to fix.
- GUC-iCal has been tested with PHP 7.4.6. It "may" work on earlier versions and "should" work on later versions.
- If PHP complains about $_ENV variables at all, check you have them enabled in your PHP.ini (variables_order = "EGPCS").

## Why is it called GUC-iCal?

For legacy reasons. Originally the script was written for Australian (only) cinema releases and sourced data from **G**reater **U**nion **C**inemas (hence the "GUC").

## Additional Credits
  
Thanks to...

- IMDb for publishing the release info (https://www.imdb.com/calendar).
- Muhammad Imangazaliev for DiDOM HTML parser (https://github.com/Imangazaliev/DiDOM).
- Kjell-Inge Gustafsson for iCalCreator class (https://github.com/iCalcreator/iCalcreator).