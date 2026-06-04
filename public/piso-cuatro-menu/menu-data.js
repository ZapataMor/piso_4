/* Piso Cuatro — datos del menú extraídos del PDF oficial.
   Orden cinematográfico: como una comida completa. */
window.PISO_MENU = [
  {
    id: "entradas",
    name: "Entradas",
    kicker: "Capítulo I · Para comenzar",
    sub: "El comienzo perfecto de una gran experiencia.",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/entradas.png",
    items: [
      { name: "Gyozas de Chivo",      desc: "Delicadas gyozas rellenas de chivo guajiro.", price: "22.000" },
      { name: "Chicharrón Guajiro",   desc: "Crocante por fuera, jugoso por dentro.",       price: "31.000" },
      { name: "Pulpo a la Parrilla",  desc: "Tentáculo sellado al carbón.",                  price: "33.000" },
      { name: "Brochetas de Salmón",  desc: "Salmón fresco en brocheta, término perfecto.",  price: "38.500" },
      { name: "Sushi",                desc: "Selección de la casa, fría y precisa.",         price: "44.000" },
      { name: "Mixdeditos",           desc: "Surtido para abrir el apetito.",                price: "40.500" }
    ]
  },
  {
    id: "compartir",
    name: "Para Compartir",
    kicker: "Capítulo II · En la mesa",
    sub: "Porque lo bueno se disfruta juntos.",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/compartir.png",
    items: [
      { name: "Jalea de Mariscos",      desc: "Mar y crocancia en una sola tabla.",        price: "45.500" },
      { name: "Parrilla Piso 4",        desc: "Nuestra parrilla insignia para dos.",       price: "59.500" },
      { name: "Salteado Mar & Tierra",  desc: "Banquete de carnes y mariscos. Para la mesa entera.", price: "132.000" }
    ]
  },
  {
    id: "pastas",
    name: "Pastas",
    kicker: "Capítulo III · Hecho a mano",
    sub: "Pastas que abrazan el alma. ¡Pide la tuya!",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/pastas.png",
    items: [
      { name: "Cremoso de Arroz con Frutos del Mar", desc: "Cremoso, marino, reconfortante.",          price: "57.000" },
      { name: "Pasta Teriyaki",                      desc: "Fusión japonesa en salsa teriyaki.",        price: "43.000" },
      { name: "Pasta Alfredo con Pollo",             desc: "Salsa Alfredo de la casa y pollo grillado.", price: "40.000" },
      { name: "Fruti di Mare",                       desc: "Frutos del mar al dente.",                   price: "51.000" }
    ],
    groups: [
      {
        title: "Menú Infantil",
        items: [
          { name: "Nuggets de Pollo",   desc: "Para los pequeños de la casa.", price: "28.000" },
          { name: "Milanesa de Pollo",  desc: "Crocante y dorada.",            price: "42.500" }
        ]
      }
    ]
  },
  {
    id: "fuertes",
    name: "Platos Fuertes",
    kicker: "Capítulo IV · El protagonista",
    sub: "Aquí empieza lo verdaderamente bueno.",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/fuertes.png",
    items: [
      { name: "Ensalada Piso 4",                  desc: "Fresca, generosa, de la casa.",        price: "35.000", feature: true },
      { name: "Baby Beef 250 grs",                desc: "Corte tierno al punto.",               price: "49.000" },
      { name: "Bife de Chorizo 380 grs",          desc: "Jugoso y marmoleado.",                 price: "70.000" },
      { name: "New York 400 grs",                 desc: "El clásico, en su máxima expresión.",  price: "98.000", feature: true },
      { name: "Steak Fusión Japonés",             desc: "Carne sellada con acento oriental.",   price: "67.000" },
      { name: "Suprema a los 4 Quesos",           desc: "Pechuga gratinada en cuatro quesos.",  price: "48.000" },
      { name: "Milanesa de Pollo",                desc: "Crocante, dorada, abundante.",         price: "42.500" },
      { name: "Pechuga Grill",                    desc: "A la parrilla, ligera y precisa.",     price: "38.000" },
      { name: "Suprema a la Parmesana",           desc: "Gratinada al estilo parmesano.",       price: "42.000" },
      { name: "Salmón Piso 4",                    desc: "Nuestro salmón insignia.",             price: "59.000", feature: true },
      { name: "Pork Rack & BBQ",                  desc: "Costillar de cerdo glaseado en BBQ.",  price: "49.500" },
      { name: "Churrasco de Cerdo",               desc: "Cerdo a la parrilla.",                 price: "38.500" },
      { name: "Pechuga Gratinada al Roquefort",   desc: "Salsa intensa de roquefort.",          price: "42.000" },
      { name: "Solomillo Peruano",                desc: "Lomo saltado de raíz peruana.",        price: "41.000" },
      { name: "Camarones al Ajillo",              desc: "Camarones salteados en ajo.",          price: "40.500" },
      { name: "Suprema Thailand",                 desc: "Pechuga con sabores tailandeses.",     price: "38.000" },
      { name: "Genova Steak",                     desc: "Corte robusto al estilo Génova.",      price: "74.000" },
      { name: "Steak de Cerdo Napolitano",        desc: "Gratinado, jamón y tomate.",           price: "42.000" }
    ]
  },
  {
    id: "hamburguesas",
    name: "Hamburguesas",
    kicker: "Capítulo V · Sin disculpas",
    sub: "Jugosas, intensas y sin disculpas.",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/hamburguesas.png",
    items: [
      { name: "Piso 4",         desc: "La hamburguesa de la casa.",          price: "32.000" },
      { name: "Cheese & BBQ",   desc: "Doble queso y salsa BBQ ahumada.",    price: "39.000" },
      { name: "Crispy Burger",  desc: "Pollo crocante, intensa y dorada.",   price: "28.000" }
    ]
  },
  {
    id: "postres",
    name: "Postres",
    kicker: "Capítulo VI · El cierre",
    sub: "El cierre perfecto para una experiencia inolvidable.",
    bg: "smoke",
    photo: "/piso-cuatro-menu/assets/food/postres.png",
    items: [
      { name: "Brownie",            desc: "Cálido, denso, de chocolate.",         price: "12.000" },
      { name: "Cheesecake",         desc: "Cremoso, con frutos rojos.",           price: "19.000" },
      { name: "Brownie con Helado", desc: "El contraste perfecto: cálido y frío.", price: "19.000" }
    ]
  },
  {
    id: "bebidas",
    name: "Bebidas",
    kicker: "Capítulo VII · Para brindar",
    sub: "Champaña de burbujas, zumos y destilados de altura.",
    bg: "bubbles",
    photo: "/piso-cuatro-menu/assets/food/whiskey.png",
    groups: [
      {
        title: "Zumos Naturales",
        items: [
          { name: "Jugo de Maracuyá",  price: "12.000" },
          { name: "Jugo de Mora",      price: "12.000" },
          { name: "Jugo de Guanábana", price: "10.000" },
          { name: "Jugo de Fresa",     price: "10.000" }
        ]
      },
      {
        title: "Limonadas",
        items: [
          { name: "Limonada Tradicional", price: "10.000" },
          { name: "Limonada de Coco",     price: "15.000" },
          { name: "Limonada Cerezada",    price: "12.000" },
          { name: "Limonada Hierbabuena", price: "10.000" }
        ]
      },
      {
        title: "Sangría",
        items: [
          { name: "Jarra de Sangría",                  price: "81.000" },
          { name: "Vino e Corozo",                     price: "79.000" },
          { name: "Jarra de Sangría & Vino e Corozo",  price: "123.000" },
          { name: "½ Jarra de Sangría & Vino e Corozo", price: "53.000" }
        ]
      },
      {
        title: "Sodas",
        items: [
          { name: "Frutos Rojos",     price: "15.000" },
          { name: "Frutas Amarillas", price: "15.000" },
          { name: "Tamarindo",        price: "15.000" },
          { name: "Hatsu",            price: "15.000" }
        ]
      },
      {
        title: "Whiskey · 750 ml",
        note: "Consultar disponibilidad",
        items: [
          { name: "Old Parr" },
          { name: "Buchanan's Master" },
          { name: "Buchanan's Deluxe" },
          { name: "Black & White" },
          { name: "Jack Daniel's", desc: "Original · Green Apple · Fire" },
          { name: "J. Walker Black" }
        ]
      },
      {
        title: "Licores Destilados",
        note: "Consultar disponibilidad",
        items: [
          { name: "Ron Viejo de Caldas" },
          { name: "Aguardiente Verde", desc: "375 ml y 750 ml" },
          { name: "Aguardiente Azul",  desc: "375 ml y 750 ml" },
          { name: "Tequila José Cuervo", desc: "750 ml" }
        ]
      },
      {
        title: "Cervezas & Otros",
        items: [
          { name: "J.P. Chanet",          price: "19.000" },
          { name: "Coronita",             price: "7.000" },
          { name: "Heineken",             price: "6.000" },
          { name: "Modelo",               price: "15.000" },
          { name: "Club Colombia",        price: "8.000" },
          { name: "Águila Original",      price: "8.000" },
          { name: "BBC Artesanal",        price: "10.000" },
          { name: "Stella Artois",        price: "10.000" },
          { name: "Smirnoff ICE",         price: "15.000" },
          { name: "La Costeña",           price: "6.000" },
          { name: "Miller Lite",          price: "6.000" },
          { name: "Escarchado adicional", price: "4.000" }
        ]
      },
      {
        title: "Gaseosas & Aguas",
        items: [
          { name: "Agua Manzana",   price: "5.000" },
          { name: "Coca-Cola",      price: "5.000" },
          { name: "Sprite",         price: "5.000" },
          { name: "Kola Román",     price: "5.000" },
          { name: "Ginger",         price: "5.000" },
          { name: "Agua Manantial", price: "5.000" },
          { name: "Quatro",         price: "5.000" },
          { name: "Soda",           price: "5.000" }
        ]
      }
    ]
  },
  {
    id: "cocteles",
    name: "Cócteles",
    kicker: "Capítulo VIII · La última copa",
    sub: "¡Eleva tu experiencia con nuestra selección exclusiva!",
    bg: "bubbles",
    photo: "/piso-cuatro-menu/assets/food/cocteles.png",
    items: [
      { name: "Blue Pineapple",         desc: "Piña y curaçao azul.",         price: "27.000" },
      { name: "Chrisrouse",             desc: "Creación de la casa.",         price: "27.000" },
      { name: "Negroni",                desc: "Amargo, clásico, elegante.",   price: "30.000" },
      { name: "Aperol Spritz",          desc: "Burbujeante y cítrico.",       price: "30.000" },
      { name: "Margarita Tradicional",  desc: "Tequila, lima y sal.",         price: "22.000" },
      { name: "Margarita de Maracuyá",  desc: "El trópico en la copa.",       price: "25.000" },
      { name: "Margarita de Fresa",     desc: "Dulce y refrescante.",         price: "25.000" },
      { name: "Daiquiri",               desc: "Ron, lima y equilibrio.",      price: "20.000" },
      { name: "Caipiroska",             desc: "Vodka y lima al natural.",     price: "20.000" },
      { name: "Mojito",                 desc: "Hierbabuena, ron y frescura.", price: "22.000" },
      { name: "Mojito de Maracuyá",     desc: "Mojito con acento tropical.",  price: "25.000" }
    ]
  }
];
