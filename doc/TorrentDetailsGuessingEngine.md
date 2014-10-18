(New) Torrent Details Guessing Engine
=============

The basic problem is that we have to determine from a torrent file's title the following information:

- Is it a video file?
- video resolution
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
  
