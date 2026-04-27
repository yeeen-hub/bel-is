<template>
  <div
    :style="{ backgroundImage: `url(${bg})` }"
    class="relative h-screen w-screen overflow-hidden bg-cover bg-center"
  >

    <!-- ── Back arrow ───────────────────────────────────────────────────── -->
    <router-link
      to="/"
      class="absolute top-4 left-4 z-50 flex items-center justify-center
             transition-all duration-300 pointer-events-auto group"
      title="Back to Landing Page"
    >
      <i class="fa-solid fa-arrow-left text-2xl sm:text-3xl text-orange-500
                group-hover:text-orange-600 group-hover:-translate-x-2 transition-all"></i>
    </router-link>

    <!-- ── "Explore Bel-is" title ─────────────────────────────────────────
         Positioned with inline style for pixel-perfect control.
         Edit titleStyle in computed: to move / resize it.
    ──────────────────────────────────────────────────────────────────────── -->
    <img
      :src="text"
      :style="titleStyle"
      class="absolute z-20 pointer-events-none h-auto"
      alt="Explore Bel-is"
    />

    <!-- ── Map centred in the viewport ──────────────────────────────────── -->
    <div class="flex items-center justify-center h-full w-full">
      <img
        ref="mapEl"
        :src="mapImg"
        class="block object-contain drop-shadow-2xl select-none"
        style="
          max-width:  min(950px, 97vw);
          max-height: calc(120vh - 1rem);
          width:  auto;
          height: auto;
        "
        alt="Bel-is Island Map"
        draggable="false"
        @load="updateMapBounds"
      />
    </div>

    <!--
      ══════════════════════════════════════════════════════════════════════
      SPOTS OVERLAY  — full-viewport layer, spots positioned in real px
      ══════════════════════════════════════════════════════════════════════
      pointer-events:none on the container so only spot divs are clickable.
      spotStyle() converts each spot's % coords → absolute px using the
      map image's actual getBoundingClientRect() — no negative-% clipping,
      no missed clicks, always locked to the map regardless of screen size.
      ══════════════════════════════════════════════════════════════════════
    -->
    <div class="absolute inset-0 pointer-events-none z-30">
      <div
        v-for="(spot, index) in spots"
        :key="index"
        class="absolute flex flex-col items-center pointer-events-auto"
        :style="spotStyle(spot)"
        @mouseenter="hoveredIndex = index"
        @mouseleave="hoveredIndex = null"
      >
        <!-- Tooltip (named/clickable spots only) -->
        <div
          v-if="hoveredIndex === index && spot.id"
          class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1
                 bg-black/60 text-xs px-3 py-1 rounded-lg whitespace-nowrap z-50"
          style="font-family: 'Chewy', cursive;"
        >
          <a
            :href="spot.link"
            target="_blank"
            class="text-white hover:text-orange-400 font-normal"
            @click.stop
          >{{ spot.name }}</a>
          <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                      border-l-4 border-r-4 border-t-4
                      border-transparent border-t-black/70"></div>
        </div>

        <!-- Icon — width is set by spotStyle, so just fill 100% -->
        <img
          :src="spot.icon"
          style="width: 100%; height: auto;"
          @click="goToLocation(spot)"
          :class="[
            'transition-all duration-300 ease-out select-none',
            spot.hover !== false
              ? 'hover:scale-125 hover:-translate-y-2 hover:drop-shadow-2xl cursor-pointer'
              : 'cursor-default'
          ]"
          draggable="false"
        />
      </div>
    </div>

    <!-- ── Social links ──────────────────────────────────────────────────── -->
    <div class="absolute bottom-4 right-4 sm:bottom-6 sm:right-8
                flex flex-col items-end gap-2 z-20">
      <h4
        class="text-orange-400 font-semibold text-xs sm:text-sm"
        style="font-family: 'Chewy', cursive;"
      >Follow us here:</h4>
      <div class="flex gap-2">
        <a href="https://facebook.com/yourpage" target="_blank"
           class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/50
                  flex items-center justify-center
                  text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-facebook-f text-sm sm:text-base"></i>
        </a>
        <a href="https://instagram.com/yourpage" target="_blank"
           class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/50
                  flex items-center justify-center
                  text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-instagram text-sm sm:text-base"></i>
        </a>
        <a href="https://linkedin.com/company/yourpage" target="_blank"
           class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/50
                  flex items-center justify-center
                  text-orange-500 hover:text-black transition-colors duration-300">
          <i class="fab fa-linkedin text-sm sm:text-base"></i>
        </a>
      </div>
    </div>

  </div>
</template>

<script>
import bgImg                   from '../../../assets/bg.png'
import mapImg                  from '../../../assets/map.png'
import textImg                 from '../../../assets/text.png'
import jambooBeachIcon         from '../../../assets/Jamboo Beach.png'
import threeSistersIcon        from '../../../assets/The 3 Sisters Beach House.png'
import denPasarIcon            from '../../../assets/Den Pasar Nasog Villa.png'
import mackysIcon              from '../../../assets/Mackys Beach Resort.png'
import belisCoveIcon           from '../../../assets/Belis Cove Beach Resort.png'
import whiteSandIcon           from '../../../assets/white sand hinugtan beach resort.png'
import hinugtanBeachResortIcon from '../../../assets/Hinugtan Beach Resort.png'
import arielsPointIcon         from '../../../assets/Ariels Point.png'
import tuburanIcon             from '../../../assets/Tuburan Cove Beach Resort.png'
import treeIcon                from '../../../assets/tree.png'
import islandIcon              from '../../../assets/island.png'
import belisIcon               from '../../../assets/belis.png'
import cliffIcon               from '../../../assets/cliff.png'
import sunIcon                 from '../../../assets/sun.png'
import hinugtanIcon            from '../../../assets/hinugtan.png'
import rockIcon                from '../../../assets/rock.png'
import shellsIcon              from '../../../assets/shells.png'
import kuboIcon                from '../../../assets/kubo.png'

export default {
  data() {
    return {
      hoveredIndex: null,
      bg:     bgImg,
      mapImg: mapImg,
      text:   textImg,

      // Actual rendered map bounds in viewport px — updated on load + every resize.
      mapBounds: { x: 0, y: 0, width: 0, height: 0 },

      /*
       * SPOT COORDINATES
       * ────────────────
       * top / left  → % of the MAP IMAGE's rendered dimensions.
       *   0%   = top-left corner of the image file
       *   100% = bottom-right corner of the image file
       *   Negative values go beyond the image edge (fine — no clipping now).
       *
       * widthPct → icon width as % of the MAP IMAGE width.
       *
       * To reposition a spot:
       *   top  up   → decrease top     top  down  → increase top
       *   left ←    → decrease left    left →     → increase left
       */
      spots: [
        // ── Clickable resort spots ────────────────────────────────────────
        {
          id: 1,
          name: "Bel-is Cove Beach Resort",
          icon: belisCoveIcon,
          link: "https://www.beliscove.com/",
          top: "28.3%", left: "51.5%", widthPct: "13%",
          hover: true
        },
        {
          id: 2,
          name: "Jamboo Beach",
          icon: jambooBeachIcon,
          link: " ",
          top: "8%", left: "73%", widthPct: "10%",
          hover: true
        },
        {
          id: 3,
          name: "The 3 Sister's Beach House",
          icon: threeSistersIcon,
          link: " ",
          top: "11%", left: "63.4%", widthPct: "10%",
          hover: true
        },
        {
          id: 4,
          name: "Den Pasar Nasog Villa",
          icon: denPasarIcon,
          link: " ",
          top: "20%", left: "70.8%", widthPct: "10%",
          hover: true
        },
        {
          id: 5,
          name: "Mackys Beach Resort",
          icon: mackysIcon,
          link: " ",
          top: "16.2%", left: "55%", widthPct: "10%",
          hover: true
        },
        {
          id: 6,
          name: "White Sand Hinugtan Beach Resort",
          icon: whiteSandIcon,
          link: " ",
          top: "39%", left: "36.7%", widthPct: "13%",
          hover: true
        },
        {
          id: 7,
          name: "Hinugtan White Beach Resort",
          icon: hinugtanBeachResortIcon,
          link: " ",
          top: "46.7%", left: "24%", widthPct: "13%",
          hover: true
        },
        {
          id: 8,
          name: "Ariel's Point",
          icon: arielsPointIcon,
          link: " ",
          top: "50.5%", left: "8%", widthPct: "10%",
          hover: true
        },
        {
          id: 9,
          name: "Tuburan Cove Beach Resort",
          icon: tuburanIcon,
          link: " ",
          top: "57.7%", left: "19.8%", widthPct: "11%",
          hover: true
        },

        // ── Decorative elements ───────────────────────────────────────────
        { name: "Tree",    icon: treeIcon,    top: "22%",   left: "63.3%",   widthPct: "9%", hover: false },
        { name: "Island",  icon: islandIcon,  top: "29.9%", left: "78%",   widthPct: "10%", hover: false },
        { name: "Belis",   icon: belisIcon,   top: "33%",   left: "63%",   widthPct: "17%", hover: true  },
        { name: "Cliff",   icon: cliffIcon,   top: "42%",   left: "65%",   widthPct: "8%", hover: false },
        { name: "Sun",     icon: sunIcon,     top: "36%",   left: "56.3%", widthPct: "10%", hover: false },
        { name: "Hinugtan",icon: hinugtanIcon,top: "50.5%", left: "39.5%",   widthPct: "17%", hover: true  },
        { name: "Tree2",   icon: treeIcon,    top: "48%",   left: "54%",   widthPct: "13%", hover: false },
        { name: "Stone",   icon: rockIcon,    top: "59%",   left: "43%",    widthPct: "12%", hover: false },
        { name: "Shells",  icon: shellsIcon,  top: "54%",   left: "31%",  widthPct: "14%", hover: false },
        { name: "Kubo",    icon: kuboIcon,    top: "70%", left: "28%",widthPct: "10%",  hover: false }
      ]
    }
  },

  computed: {
    /*
     * TITLE POSITION & SIZE — edit these three values:
     *
     *   top   → distance from the top of the screen
     *   left  → distance from the left of the screen
     *   width → clamp(min, preferred, max) — scales with viewport width
     *
     * Use any CSS unit: '3rem', '48px', '5vw', etc.
     */
    titleStyle() {
      return {
        top:   '3rem',
        left:  '4rem',
        width: 'clamp(170px, 35vw, 480px)',
      }
    }
  },

  mounted() {
    this.updateMapBounds()
    // Re-measure whenever the browser resizes (handles zoom, orientation change, etc.)
    this._ro = new ResizeObserver(this.updateMapBounds)
    this._ro.observe(document.documentElement)
  },

  beforeUnmount() {
    this._ro?.disconnect()
  },

  methods: {
    updateMapBounds() {
      const el = this.$refs.mapEl
      if (!el) return
      const r = el.getBoundingClientRect()
      this.mapBounds = { x: r.left, y: r.top, width: r.width, height: r.height }
    },

    /*
     * Convert spot % coords → absolute px relative to the viewport.
     * The spot div is placed in the full-screen overlay (absolute inset-0),
     * so left/top px values here are viewport-relative — no clipping ever.
     */
    spotStyle(spot) {
      const { x, y, width, height } = this.mapBounds
      const leftPx  = x + (parseFloat(spot.left)    / 100) * width
      const topPx   = y + (parseFloat(spot.top)     / 100) * height
      const widthPx =     (parseFloat(spot.widthPct)/ 100) * width
      return {
        left:  leftPx  + 'px',
        top:   topPx   + 'px',
        width: widthPx + 'px',
      }
    },

    goToLocation(spot) {
      if (!spot || !spot.id) return
      this.$router.push(`/location/${spot.id}`)
    }
  }
}
</script>