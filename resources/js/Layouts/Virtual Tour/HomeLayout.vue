<template>
  <div
    :style="{ backgroundImage: `url(${bg})` }"
    class="relative h-screen overflow-hidden bg-cover bg-center m-0 p-0"
  >
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
export default {
  data() {
    return {
      hoveredIndex: null,
      bg: new URL('@/assets/bg.png', import.meta.url).href,
      mapImg: new URL('@/assets/map.png', import.meta.url).href,
      text: new URL('@/assets/text.png', import.meta.url).href,
      

      spots: [
        {
          id: 2,
          name: "Jamboo Beach",
          icon: new URL('@/assets/Jamboo Beach.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "2%",
          left: "65%",
          hover: true
        },
        {
          id: 3,
          name: "The 3 Sister's Beach House",
          icon: new URL('@/assets/The 3 Sisters Beach House.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "7%",
          left: "59%",
          hover: true
        },
        {
          id: 4,
          name: "Den Pasar Nasog Villa",
          icon: new URL('@/assets/Den Pasar Nasog Villa.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "15%",
          left: "65%",
          hover: true
        },
        {
          id: 5,
          name: "Mackys Beach Resort",
          icon: new URL('@/assets/Mackys Beach Resort.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "15%",
          left: "54%",
          hover: true
        },
        {
          id: 1,
          name: "Bel-is Cove Beach Resort",
          icon: new URL('@/assets/Belis Cove Beach Resort.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "29%",
          left: "51%",
          hover: true
        },
        {
          id: 6,
          name: "White Sand Hinugtan Beach Resort",
          icon: new URL('@/assets/white sand hinugtan beach resort.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "38%",
          left: "42%",
          size: "w-[110px]",
          hover: true
        },
        {
          id: 7,
          name: "Hinugtan White Beach Resort",
          icon: new URL('@/assets/Hinugtan Beach Resort.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "49%",
          left: "32%",
          size: "w-[120px]",
          hover: true
        },
        {
          id: 8,
          name: "Ariel's Point",
          icon: new URL('@/assets/Ariels Point.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "51%",
          left: "24%",
          hover: true
        },
        {
          id: 9,
          name: "Tuburan Cove Beach Resort",
          icon: new URL('@/assets/Tuburan Cove Beach Resort.png', import.meta.url).href,
          link: "https://www.beliscove.com/",
          top: "64%",
          left: "28%",
          size: "w-[90px]",
          hover: true
        },
        {
          name: "Tree",
          icon: new URL('@/assets/tree.png', import.meta.url).href,
          top: "20%",
          left: "59%",
          hover: false
        },
        {
          name: "Island",
          icon: new URL('@/assets/island.png', import.meta.url).href,
          top: "28%",
          left: "64%",
          size: "w-[120px]",
          hover: false
        },
        {
          name: "Belis",
          icon: new URL('@/assets/belis.png', import.meta.url).href,
          top: "35%",
          left: "59%",
          size: "w-[120px]",
          hover: true
        },
        {
          name: "Cliff",
          icon: new URL('@/assets/cliff.png', import.meta.url).href,
          top: "43%",
          left: "57%",
          hover: false
        },
        {
          name: "Sun",
          icon: new URL('@/assets/sun.png', import.meta.url).href,
          top: "45%",
          left: "49%",
          hover: false
        },
        {
          name: "Hinugtan",
          icon: new URL('@/assets/hinugtan.png', import.meta.url).href,
          top: "55%",
          left: "41%",
          size: "w-[150px]",
          hover: true
        },
        {
          name: "Tree",
          icon: new URL('@/assets/tree.png', import.meta.url).href,
          top: "57%",
          left: "51%",
          hover: false
        },
        {
          name: "Stone",
          icon: new URL('@/assets/rock.png', import.meta.url).href,
          top: "61%",
          left: "45%",
          size: "w-[110px]",
          hover: false
        },
        {
          name: "Shells",
          icon: new URL('@/assets/shells.png', import.meta.url).href,
          top: "58%",
          left: "37%",
          size: "w-[110px]",
          hover: false
        },
        {
          name: "Kubo",
          icon: new URL('@/assets/kubo.png', import.meta.url).href,
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