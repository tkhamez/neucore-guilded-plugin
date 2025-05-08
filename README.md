# Neucore Guilded Plugin

_Needs [Neucore](https://github.com/tkhamez/neucore) version 2.6.0 or higher._

This plugin will link [Guilded](https://www.guilded.gg) accounts to 
[Neucore](https://github.com/tkhamez/neucore) accounts.

**Note: This is a proof of concept, it's not fully functional yet.**

## Setup Guilded Server

- Create a public "auth" channel where everyone can write.
- Create a bot with permission to read messages in the "auth" channel.
- Generate an API token for the bot.

## Install Plugin

- In Neucore, create a new Guilded service in the plugin administration.
- Add the required values to the configuration data.

## Development environment

```
docker build --tag neucore-plugin-guilded .
docker run -it --mount type=bind,source="$(pwd)",target=/app --workdir /app neucore-plugin-guilded /bin/sh
```
