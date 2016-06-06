# Agar.io Clone (Acloneio)

A recreation of [agar.io](http://agar.io/) by Sami Abood and Alex Matson.


Currently a work in progress, this project utilizes PHP to run the server and the
Ratchet library to enable real-time multiplayer through WebSocket.


Javascript and HTML is used for the front-end.

---
### Testing

If you'd like to test what we have so far, you need to install PHP and [Ratchet](http://socketo.me/docs/install).

After that is all set up, simply execute `php bin\serverScript.php` in the root directory,
and open index.html in two different browsers.

---
### To-do List

##### Game-play Mechanics
- [x] Enable real-time multiplayer connections
- [ ] Allow multiple players to join instead of 2
- [ ] Implement the point system
- [ ] Add randomly generated food dots around the map
- [ ] Program the player's size to change as they gain points (by eating the dots)
- [ ] Make the player's speed proportional to their size (larger balls move slower)
- [ ] Allow the player to eat another player by fully overlapping them
- [ ] Players can eject a tiny bit of their mass in the direction of their cursor by pressing 'W'
- [ ] Players can split their size in half by pressing spacebar -- now they control 2 balls that both follow the cursor

##### Aesthetics and Details
- [ ] Expand the map's size beyond the screen
- [ ] Create a front-page where players type in a username and click a button to enter the map
- [ ] Add a leaderboard of points in the top corner
- [ ] Add "virus cells" -- green spiked elements that will pop a player if they are bigger than the virus and overlap too much

---
Created in Summer 2016.
