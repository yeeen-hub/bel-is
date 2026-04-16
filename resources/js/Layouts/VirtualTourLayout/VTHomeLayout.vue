<template>
  <div
    :style="{ backgroundImage: `url(${bg})` }"
    class="relative h-screen overflow-hidden bg-cover bg-center m-0 p-0"
  >

    <router-link
      to="/"
      class="absolute top-6 left-6 z-50 flex items-center justify-center transition-all duration-300 pointer-events-auto group"
      title="Back to Landing Page"
    >
      <i class="fa-solid fa-arrow-left text-3xl text-orange-500 group-hover:text-orange-600 group-hover:-translate-x-2 transition-all"></i>
    </router-link>

    <div class="absolute inset-0 flex items-center justify-center z-10">
      <img
        :src="mapImg"
        class="w-[70vw] max-w-[940px] max-h-[140vh] object-contain drop-shadow-2xl"
      />


    <img
      :src="text"
      class="absolute top-[5rem] left-10 w-[465px] h-auto z-20"
      alt="logo"
    />

    <div
      v-for="(spot, index) in spots"
      :key="index"
      class="absolute flex flex-col items-center cursor-pointer"
      :style="{ top: spot.top, left: spot.left }"
      @mouseenter="hoveredIndex = index"
      @mouseleave="hoveredIndex = null"
    >
    <div
      v-if="hoveredIndex === index && spot.id"
      class="absolute -top-10 left-1/2 -translate-x-1/2 bg-black/60 text-xs px-3 py-1 rounded-lg whitespace-nowrap z-50"
      style="font-family: 'Chewy', cursive;"
    >
      <a
        :href="spot.link"
        target="_blank"
        class="text-white hover:text-orange-400 font-normal"
        @click.stop
      >
        {{ spot.name }}
      </a>
      <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-black/70"></div>
    </div>

      <img
        :src="spot.icon"
        @click="goToLocation(spot)"
        :class="[
          spot.size || (spot.name === 'Bel-is Cove Beach Resort' ? 'w-[112px]' : 'w-[84px]'),
          'h-auto transition-all duration-300 ease-out',
          spot.hover !== false
            ? 'hover:scale-125 hover:-translate-y-2 hover:drop-shadow-2xl cursor-pointer'
            : ''
        ]"
      />
    </div>
    </div>
    <div class="absolute bottom-6 right-8 flex flex-col items-end gap-2 z-20">
      <h4 class="text-orange-400 font-semibold text-sm" style="font-family: 'Chewy', cursive;">Follow us here:</h4>
      <div class="flex gap-2">
        <a href="https://facebook.com/yourpage" target="_blank"
          class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://instagram.com/yourpage" target="_blank"
          class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="https://linkedin.com/company/yourpage" target="_blank"
          class="w-10 h-10 rounded-full bg-white/50 flex items-center justify-center text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-linkedin"></i>
        </a>
      </div>
    </div>
  </div>

</template>

<script>
// ✅ FIX: Import all assets statically at the top.
// Previously, new URL('@/assets/...', import.meta.url).href was used inside data(),
// which is a Vite-only pattern and does NOT work with Webpack/Laravel Mix —
// it resolves to just the bare filename (e.g. "bg.png") causing 404 errors.
import bgImg from '../../../assets/bg.png'
import mapImg from '../../../assets/map.png'
import textImg from '../../../assets/text.png'
import jambooBeachIcon from '../../../assets/Jamboo Beach.png'
import threeSistersIcon from '../../../assets/The 3 Sisters Beach House.png'
import denPasarIcon from '../../../assets/Den Pasar Nasog Villa.png'
import mackysIcon from '../../../assets/Mackys Beach Resort.png'
import belisCoveIcon from '../../../assets/Belis Cove Beach Resort.png'
import whiteSandIcon from '../../../assets/white sand hinugtan beach resort.png'
import hinugtanBeachResortIcon from '../../../assets/Hinugtan Beach Resort.png'
import arielsPointIcon from '../../../assets/Ariels Point.png'
import tuburanIcon from '../../../assets/Tuburan Cove Beach Resort.png'
import treeIcon from '../../../assets/tree.png'
import islandIcon from '../../../assets/island.png'
import belisIcon from '../../../assets/belis.png'
import cliffIcon from '../../../assets/cliff.png'
import sunIcon from '../../../assets/sun.png'
import hinugtanIcon from '../../../assets/hinugtan.png'
import rockIcon from '../../../assets/rock.png'
import shellsIcon from '../../../assets/shells.png'
import kuboIcon from '../../../assets/kubo.png'

export default {
  data() {
    return {
      hoveredIndex: null,
      bg: bgImg,
      mapImg: mapImg,
      text: textImg,

      spots: [
        {
          id: 1,
          name: "Bel-is Cove Beach Resort",
          icon: belisCoveIcon,
          link: "https://www.beliscove.com/",
          top: "29%",
          left: "51%",
          hover: true
        },
        {
          id: 2,
          name: "Jamboo Beach",
          icon: jambooBeachIcon,
          link: " ",
          top: "2%",
          left: "65%",
          hover: true
        },
        {
          id: 3,
          name: "The 3 Sister's Beach House",
          icon: threeSistersIcon,
          link: " ",
          top: "7%",
          left: "59%",
          hover: true
        },
        {
          id: 4,
          name: "Den Pasar Nasog Villa",
          icon: denPasarIcon,
          link: " ",
          top: "15%",
          left: "65%",
          hover: true
        },
        {
          id: 5,
          name: "Mackys Beach Resort",
          icon: mackysIcon,
          link: " ",
          top: "15%",
          left: "54%",
          hover: true
        },
        {
          id: 6,
          name: "White Sand Hinugtan Beach Resort",
          icon: whiteSandIcon,
          link: " ",
          top: "38%",
          left: "42%",
          size: "w-[110px]",
          hover: true
        },
        {
          id: 7,
          name: "Hinugtan White Beach Resort",
          icon: hinugtanBeachResortIcon,
          link: " ",
          top: "49%",
          left: "32%",
          size: "w-[120px]",
          hover: true
        },
        {
          id: 8,
          name: "Ariel's Point",
          icon: arielsPointIcon,
          link: " ",
          top: "51%",
          left: "24%",
          hover: true
        },
        {
          id: 9,
          name: "Tuburan Cove Beach Resort",
          icon: tuburanIcon,
          link: " ",
          top: "64%",
          left: "28%",
          size: "w-[90px]",
          hover: true
        },
        {
          name: "Tree",
          icon: treeIcon,
          top: "20%",
          left: "59%",
          hover: false
        },
        {
          name: "Island",
          icon: islandIcon,
          top: "28%",
          left: "64%",
          size: "w-[120px]",
          hover: false
        },
        {
          name: "Belis",
          icon: belisIcon,
          top: "35%",
          left: "59%",
          size: "w-[120px]",
          hover: true
        },
        {
          name: "Cliff",
          icon: cliffIcon,
          top: "43%",
          left: "57%",
          hover: false
        },
        {
          name: "Sun",
          icon: sunIcon,
          top: "45%",
          left: "49%",
          hover: false
        },
        {
          name: "Hinugtan",
          icon: hinugtanIcon,
          top: "55%",
          left: "41%",
          size: "w-[150px]",
          hover: true
        },
        {
          name: "Tree",
          icon: treeIcon,
          top: "57%",
          left: "51%",
          hover: false
        },
        {
          name: "Stone",
          icon: rockIcon,
          top: "61%",
          left: "45%",
          size: "w-[110px]",
          hover: false
        },
        {
          name: "Shells",
          icon: shellsIcon,
          top: "58%",
          left: "37%",
          size: "w-[110px]",
          hover: false
        },
        {
          name: "Kubo",
          icon: kuboIcon,
          top: "72%",
          left: "34%",
          size: "w-[110px]",
          hover: false
        }
      ]
    }
  },
  methods: {
    goToLocation(spot) {
      if (!spot || !spot.id) return
      this.$router.push(`/location/${spot.id}`)
    }
  }
}
</script>