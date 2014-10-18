#(New) Torrent Title Parsing Engine

##Abstract

The basic problem is that we have to determine from a torrent file's title the following information:

- Is it a video file?
- Is it episodic or a one-off video like a movie or film?
  - If it is episodic, what is the unique identifier?
    - Does it use date or sequential numbering?
      - season number, if present--otherwise assume it is season 1 or season 0 for anime
      - episode number, if present
    - How many episodes are in the torrent?
      - single episodes
      - batches of episodes (like episodes 4-6 ripped from BluRay disc 2 from a boxed set)
      - full season batches
      - special episodes (like 4.5, OVAs, etc.)
  - one-off videos like movies
- What is the quality?
  - video resolution
  - Is it a repack or proper or rerip or version 2, etc.?

Additionally, I'd like to add these details someday, though they can be controlled fairly easily using the Favorites title filtering:

- What is the language(s)?
  - Language dubbed?
  - Language subbed?

The original code from TorrentWatch and TorrentWatch-X basically uses a single pass to try to determine all of the details. I am convinced it will take multiple passes, each one searching for specific answers to the questions above. The passes will probably have to be performed in a specific order to reduce collisions. For instance, some torrents have both the season and episode numbering plus the air date; the season and episode numbering should trump the air date. In another case, there may be a three-digit date MDD that is indistinguishable from a three-digit episode number--which to choose? And in yet another case, I saw Adobe Acrobat 11.0.1, which could be misinterpreted as YY.M.D.

These decisions can't be made easily just using regular expression logic. Better to tap into some of the existing date interpreter modules already out there that use sophisticated logic to parse dates. This leads into my next realization...

I also believe we will have to normalize the many different styles of representing a date or episode or season into rigid formats. This is easy to do with dates, since date object classes are readily available in PHP. It's a bit tougher with seasons and episodes mainly due to the possibility of batching. I suppose a single torrent that contains a batch of episodes must be represented as a list of individual episodes, and we only really care about the last episode in the batch when auto-downloading the next episode or the last season number if downloading full season batches.

---

I will be rewriting the engine here to do a better job of parsing torrent titles and normalizing them into actionable information. I'll also outright reject torrent titles that don't parse and give the user the choice to hide non-parseable torrents from the list.

##The logic

(Discussion of the logic behind the engine with the intent of forming pseudo-code.)
