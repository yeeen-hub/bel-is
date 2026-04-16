import belisAerial from "../../assets/belisaerial.jpg";
import belisBeach from "../../assets/panorama.jpg";
import belisPool from "../../assets/panorama.jpg";
import belisHallway from "../../assets/panorama.jpg";
import belisLobby from "../../assets/panorama.jpg";
import belisRoom from "../../assets/panorama.jpg";
import belisRestaurant from "../../assets/panorama.jpg";

import jambooAerial from "../../assets/jambooaerial.jpg";
import jambooBeach from "../../assets/panorama.jpg";
import jambooPool from "../../assets/panorama.jpg";
import jambooHallway from "../../assets/panorama.jpg";
import jambooLobby from "../../assets/panorama.jpg";
import jambooRoom from "../../assets/panorama.jpg";
import jambooRestaurant from "../../assets/panorama.jpg";

// Add unique aerial/image imports for new locations as you have the assets ready.
// For now they share panorama.jpg as a placeholder — replace with real images.

export const locations = [
  {
    id: 1,
    name: "Bel-is Cove Beach Resort",
    category: "Belis Beach",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ id: 2 must match the spot id in VTHomeLayout.vue
    id: 2,
    name: "Jamboo Beach",
    category: "Jamboo Beach",
    images: {
      aerial: jambooAerial,
      beach: jambooBeach,
      pool: jambooPool,
      hallway: jambooHallway,
      lobby: jambooLobby,
      room: jambooRoom,
      restaurant: jambooRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "The 3 Sister's Beach House" found no data
    id: 3,
    name: "The 3 Sister's Beach House",
    category: "Boracay",
    images: {
      aerial: belisAerial,      // replace with real asset when available
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "Den Pasar Nasog Villa" found no data
    id: 4,
    name: "Den Pasar Nasog Villa",
    category: "Boracay",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "Mackys Beach Resort" found no data
    id: 5,
    name: "Mackys Beach Resort",
    category: "Boracay",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "White Sand Hinugtan Beach Resort" found no data
    id: 6,
    name: "White Sand Hinugtan Beach Resort",
    category: "Hinugtan Beach",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "Hinugtan White Beach Resort" found no data
    id: 7,
    name: "Hinugtan White Beach Resort",
    category: "Hinugtan Beach",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "Ariel's Point" found no data
    id: 8,
    name: "Ariel's Point",
    category: "Boracay",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
  {
    // ✅ Previously missing — clicking "Tuburan Cove Beach Resort" found no data
    id: 9,
    name: "Tuburan Cove Beach Resort",
    category: "Tuburan",
    images: {
      aerial: belisAerial,
      beach: belisBeach,
      pool: belisPool,
      hallway: belisHallway,
      lobby: belisLobby,
      room: belisRoom,
      restaurant: belisRestaurant,
    },
  },
];