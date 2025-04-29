// Fonctions pour index.html
function adjustCount(type, change) {
    const input = document.getElementById(type);
    let value = parseInt(input.value) + change;
    if (value < input.min) value = parseInt(input.min);
    input.value = value;
}

function searchOffers() {
    const country = document.getElementById('country').value;
    const checkIn = document.getElementById('check-in').value;
    const checkOut = document.getElementById('check-out').value;
    const adults = document.getElementById('adults').value;
    const children = document.getElementById('children').value;
    const minPrice = document.getElementById('min-price').value;
    const maxPrice = document.getElementById('max-price').value;

    localStorage.setItem('searchData', JSON.stringify({
        country,
        checkIn, 
        checkOut,
        adults,
        children,
        minPrice,
        maxPrice
    }));

    window.location.href = 'destination.html';
}

// Données des destinations par pays (5 destinations chacun)
const destinationsByCountry = {
    'Tunisie': [
        {
            city: 'Sidi Bou Saïd',
            image: 'https://www.tunisienumerique.com/wp-content/uploads/2024/04/Sidi-1.jpg',
            description: 'Village emblématique bleu et blanc',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Hôtel 4* vue mer', 'Petit-déjeuner', 'Guide touristique']
        },
        {
            city: 'Hammamet',
            image: 'https://www.momondo.fr/himg/7a/e5/72/expediav2-179907-f4f07d-547105.jpg',
            description: 'Station balnéaire réputée',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '5 nuits / 6 jours',
            features: ['Hôtel 5* spa', 'Demi-pension', 'Plage privée']
        },
        {
            city: 'Djerba',
            image: 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/18/df/94/90/piscine-central.jpg?w=1200&h=-1&s=1',
            description: 'Île aux plages paradisiaques',
            originalPrice: '450€',
            currentPrice: '380€',
            duration: '4 nuits / 5 jours',
            features: ['Hôtel 4* piscine', 'All-inclusive', 'Excursions']
        },
        {
            city: 'Tunis',
            image: 'https://www.thd.tn/wp-content/uploads/2015/12/forbes1-1.jpg',
            description: 'Capitale vibrante',
            originalPrice: '300€',
            currentPrice: '240€',
            duration: '3 nuits / 4 jours',
            features: ['Hôtel centre-ville', 'Médina', 'Musées']
        },
        {
            city: 'Tozeur',
            image: 'https://discovertozeur.com/wp-content/uploads/2023/10/Untitled-3.png',
            description: 'Oasis du désert',
            originalPrice: '380€',
            currentPrice: '310€',
            duration: '4 nuits / 5 jours',
            features: ['Hôtel palmeraie', 'Balade en dromadaire', 'Coucher de soleil']
        }
    ],
    'France': [
        {
            city: 'Paris',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTQAudOk1Z4SsENi9WfB2rQeepH-7YyWdcbMg&s',
            description: 'La ville lumière',
            originalPrice: '600€',
            currentPrice: '450€',
            duration: '3 nuits / 4 jours',
            features: ['Tour Eiffel', 'Louvre', 'Croisière Seine']
        },
        {
            city: 'Nice',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQddbxsPPuzUe6FXyBU8wKc9kAiadFfhAtdWQ&s',
            description: 'Riviera française',
            originalPrice: '550€',
            currentPrice: '420€',
            duration: '4 nuits / 5 jours',
            features: ['Promenade des Anglais', 'Vieille ville', 'Plages']
        },
        {
            city: 'Lyon',
            image: 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/14/da/01/47/vieux-lyon.jpg?w=900&h=500&s=1',
            description: 'Capitale gastronomique',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '3 nuits / 4 jours',
            features: ['Vieux Lyon', 'Parc de la Tête d\'Or', 'Bouchons lyonnais']
        },
        {
            city: 'Marseille',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8RIiodrgFL6YHeg2idSaiVIXQsv6UYgec-A&s',
            description: 'Port méditerranéen',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Vieux-Port', 'Calanques', 'MuCEM']
        },
        {
            city: 'Bordeaux',
            image: 'https://images.winalist.com/blog/wp-content/uploads/2020/02/26144710/AdobeStock_55602461.jpeg',
            description: 'Cité du vin',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '3 nuits / 4 jours',
            features: ['Cité du Vin', 'Place de la Bourse', 'Vignobles']
        }
    ],
    'Rome': [
        {
            city: 'Rome',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8zlN2MynhkvSeX452Oxe-heMUuK_3iJMPcQ&s',
            description: 'La ville éternelle',
            originalPrice: '500€',
            currentPrice: '380€',
            duration: '4 nuits / 5 jours',
            features: ['Colisée', 'Vatican', 'Fontaine de Trevi']
        },
        {
            city: 'Venise',
            image: 'https://www.petitfute.com/medias/mag/12417/originale/AdobeStock_293414266-1024x683.jpeg',
            description: 'Cité des canaux',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '3 nuits / 4 jours',
            features: ['Gondoles', 'Place Saint-Marc', 'Pont du Rialto']
        },
        {
            city: 'Florence',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRndQBTbPaPQbllA-nfogHOMz41PvXwHFa3hw&s',
            description: 'Berceau Renaissance',
            originalPrice: '450€',
            currentPrice: '370€',
            duration: '3 nuits / 4 jours',
            features: ['Duomo', 'Galerie des Offices', 'Ponte Vecchio']
        },
        {
            city: 'Milan',
            image: 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/10/f5/ab/76/photo1jpg.jpg?w=600&h=-1&s=1',
            description: 'Capitale de la mode',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '3 nuits / 4 jours',
            features: ['Dôme', 'Galleria Vittorio', 'La Scala']
        },
        {
            city: 'Naples',
            image: 'https://www.tourmag.com/photo/art/grande/75519332-52996939.jpg?v=1695985198',
            description: 'Port historique',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Pompéi', 'Vésuve', 'Pizza napolitaine']
        }
        
    ],
    'Inde': [
        {
            city: 'New Delhi',
            image: 'https://cdn.getyourguide.com/img/location/533591d4b943b.jpeg/99.jpg',
            description: 'Capitale vibrante au riche patrimoine',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '5 nuits / 6 jours',
            features: ['Temple du Lotus', 'Qutub Minar', 'Marché de Chandni Chowk']
        },
        {
            city: 'Mumbai',
            image: 'https://a0.muscache.com/im/pictures/INTERNAL/INTERNAL-Mumbai/original/8fbdc98d-2b32-4838-a2cd-d4654d76b746.jpeg',
            description: 'Métropole effervescente et cinématographique',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '4 nuits / 5 jours',
            features: ['Gateway of India', 'Plage de Juhu', 'Bollywood Tour']
        },
        {
            city: 'Jaipur',
            image: 'https://s7ap1.scene7.com/is/image/incredibleindia/hawa-mahal-jaipur-rajasthan-city-1-hero?qlt=82&ts=1726660605161',
            description: 'Ville rose du Rajasthan',
            originalPrice: '420€',
            currentPrice: '340€',
            duration: '4 nuits / 5 jours',
            features: ['Palais des Vents', 'Fort d\'Amber', 'Observatoire Jantar Mantar']
        },
        {
            city: 'Varanasi',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQefk-VTAjxiYS551jNkc_5TE_cFv1RXP7YYw&s',
            description: 'Ville sainte sur les rives du Gange',
            originalPrice: '380€',
            currentPrice: '300€',
            duration: '3 nuits / 4 jours',
            features: ['Ghats de Bénarès', 'Cérémonie du Gange Aarti', 'Temple Kashi Vishwanath']
        },
        {
            city: 'Goa',
            image: 'https://www.telegraph.co.uk/content/dam/travel/2025/01/11/TELEMMGLPICT000407251404_17365976158650_trans_NvBQzQNjv4BqpVlberWd9EgFPZtcLiMQf0Rf_Wk3V23H2268P_XkPxc.jpeg?imwidth=680',
            description: 'Plages paradisiaques et héritage portugais',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '5 nuits / 6 jours',
            features: ['Plage de Palolem', 'Vieille Goa', 'Marchés aux épices']
        }
    ],
    'Maldives': [
        {
            city: 'Malé',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1HYP-XC1yGIlScYmRQOO0-5ZY0BJhmAAOv7Gu-WeYAP72yPJ7kHCvwGTYZr--rieMzyM&usqp=CAU',
            description: 'Capitale animée des Maldives',
            originalPrice: '1200€',
            currentPrice: '950€',
            duration: '4 nuits / 5 jours',
            features: ['Mosquée du Vendredi', 'Marché aux poissons', 'Excursions vers les îles']
        },
        {
            city: 'Ari Atoll',
            image: 'https://www.manta.ch/assets/_processed_/6/a/csm_MantaGallery_6fcfcf1846.jpg',
            description: 'Plaongée parmi les raies manta',
            originalPrice: '1800€',
            currentPrice: '1500€',
            duration: '5 nuits / 6 jours',
            features: ['Resort de luxe', 'Snorkeling avec requins-baleines', 'Spa sur pilotis']
        },
        {
            city: 'Baa Atoll',
            image: 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/94866475.jpg?k=059d3ea2d00bab1b602612df137769bea52d6cd2097a549774125dc99cab69b8&o=&hp=1',
            description: 'Réserve de biosphère UNESCO',
            originalPrice: '2000€',
            currentPrice: '1700€',
            duration: '6 nuits / 7 jours',
            features: ['Villas sur l\'eau', 'Restaurant sous-marin', 'Observation des raies manta']
        },
        {
            city: 'Lhaviyani Atoll',
            image: 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/17/f8/1d/1b/kudadoo-maldives-private.jpg?w=1400&h=1400&s=1',
            description: 'Plages de sable immaculé',
            originalPrice: '1600€',
            currentPrice: '1300€',
            duration: '5 nuits / 6 jours',
            features: ['Spa océanique', 'Piscine à débordement', 'Excursion en hydravion']
        },
        {
            city: 'Vaavu Atoll',
            image: 'https://res.cloudinary.com/zublu/image/fetch/f_webp,w_1200,q_auto/https://www.zubludiving.com/images/Maldives/Vaavu-and-Meemu-Atolls/Vaavu-Atoll-Maldives-Scuba-Diving-8.jpg',
            description: 'Destination préservée et authentique',
            originalPrice: '1400€',
            currentPrice: '1100€',
            duration: '4 nuits / 5 jours',
            features: ['Croisière au coucher du soleil', 'Plongée de nuit', 'Village local']
        }
    ],
    
    'Russie': [
        {
            city: 'Moscou',
            image: 'https://littleweekends.fr/wp-content/uploads/2022/12/Basile-le-Bienheureux-Moscou.jpg',
            description: 'Cœur historique de la Russie',
            originalPrice: '850€',
            currentPrice: '720€',
            duration: '5 nuits / 6 jours',
            features: ['Place Rouge', 'Kremlin', 'Métro de Moscou']
        },
        {
            city: 'Saint-Pétersbourg',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSpyKEG8pkHfm0AnABccp_781zpu9dnWcy1qw&s',
            description: 'Venise du Nord aux canaux romantiques',
            originalPrice: '780€',
            currentPrice: '650€',
            duration: '4 nuits / 5 jours',
            features: ['Musée de l\'Ermitage', 'Palais de Peterhof', 'Nuits blanches']
        },
        {
            city: 'Kazan',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBTugDvD6O_FToLjYCACBgxvsy9gKaJXk4xg&s',
            description: 'Rencontre entre l\'Europe et l\'Asie',
            originalPrice: '600€',
            currentPrice: '490€',
            duration: '3 nuits / 4 jours',
            features: ['Kremlin de Kazan', 'Temple des religions', 'Culture tatare']
        },
        {
            city: 'Vladivostok',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ_pikkNC-YxbmsTbVDqHttTLQMq7E6vcTHnQ&s',
            description: 'Port du Pacifique et terminus du Transsibérien',
            originalPrice: '950€',
            currentPrice: '800€',
            duration: '4 nuits / 5 jours',
            features: ['Pont du Golden Horn', 'Forteresse maritime', 'Gastronomie de crabes']
        },
        {
            city: 'Sotchi',
            image: 'https://russieautrement.com/upload/medialibrary/6b4/sotchi.jpg',
            description: 'Station balnéaire de la mer Noire',
            originalPrice: '700€',
            currentPrice: '580€',
            duration: '4 nuits / 5 jours',
            features: ['Parc Olympique', 'Jardins botaniques', 'Montagnes du Caucase']
        }
    ],
    'Tchad': [
        {
            city: 'N\'Djaména',
            image: 'https://rendodjo.mondoblog.org/files/2013/03/Ndjam.jpg', // Remplacez par une vraie image
            description: 'Capitale animée sur les rives du Chari',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '3 nuits / 4 jours',
            features: ['Grande Mosquée', 'Marché central', 'Musée national']
        },
        {
            city: 'Lac Tchad',
            image: 'https://s.rfi.fr/media/display/1887d58c-0fd0-11ea-93d3-005056a99247/w:980/p:16x9/000_da1m4_0.jpg',
            description: 'Site naturel exceptionnel',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '4 nuits / 5 jours',
            features: ['Excursions en pirogue', 'Observation d\'oiseaux', 'Villages flottants']
        },
        {
            city: 'Sarh',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm8hv1eWDJz1Ct0b8C0_TVAXjDvrzlekngFg&s',
            description: 'Ville du sud aux paysages verdoyants',
            originalPrice: '380€',
            currentPrice: '300€',
            duration: '3 nuits / 4 jours',
            features: ['Parc de Zakouma (proche)', 'Culture Sara', 'Marché artisanal']
        },
        {
            city: 'Massif du Tibesti',
            image: 'https://www.tresorsdumonde.fr/wp-content/uploads/2016/11/Massif-du-Tibesti1.jpg',
            description: 'Montagnes lunaires du Sahara',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '5 nuits / 6 jours',
            features: ['Randonnées', 'Peintures rupestres', 'Pic Toussidé']
        },
        {
            city: 'Abeché',
            image: 'https://cdn.britannica.com/35/138735-004-DC50062C/Marketplace-Abeche-Chad.jpg',
            description: 'Ancienne capitale du Ouaddaï',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Palais du sultan', 'Mosquée historique', 'Artisanat local']
        }
    ],
    'Espagne': [
        {
            city: 'Barcelone',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRWZZzserxRJw39YSiSPwCeX8bFmSJmb7sE3A&s',
            description: 'Ville de Gaudí',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '4 nuits / 5 jours',
            features: ['Sagrada Familia', 'Parc Güell', 'Las Ramblas']
        },
        {
            city: 'Madrid',
            image: 'https://www.okvoyage.com/wp-content/uploads/2019/10/visiter-Madrid.jpg',
            description: 'Capitale animée',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '3 nuits / 4 jours',
            features: ['Palais Royal', 'Musée du Prado', 'Plaza Mayor']
        },
        {
            city: 'Séville',
            image: 'https://image.urlaubspiraten.de/640/image/upload/v1617096024/Impressions%20and%20Other%20Assets/shutterstock_1840522828_tcbevm.webp',
            description: 'Ville andalouse',
            originalPrice: '380€',
            currentPrice: '300€',
            duration: '3 nuits / 4 jours',
            features: ['Alcázar', 'Cathédrale', 'Quartier Santa Cruz']
        },
        {
            city: 'Valence',
            image: 'https://www.voyageavecnous.fr/wp-content/uploads/2020/01/visiter-valence.jpg',
            description: 'Cité des arts et sciences',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Cité des Arts', 'Plage de Malvarrosa', 'Paella valencienne']
        },
        {
            city: 'Grenade',
            image: 'https://alhambragrenade.fr/images/grenade-ville.jpg',
            description: 'Joyau andalou',
            originalPrice: '320€',
            currentPrice: '250€',
            duration: '3 nuits / 4 jours',
            features: ['Alhambra', 'Quartier Albaicín', 'Vues sur Sierra Nevada']
        }
    ],
    'Japon': [
        {
            city: 'Tokyo',
            image: 'https://q-xx.bstatic.com/xdata/images/city/608x352/619746.webp?k=ff577d4b01bf1332a711e09ad9b320b516b4cbfca9122d20d9dfc6a6e3fab9d2&o=',
            description: 'Métropole futuriste',
            originalPrice: '800€',
            currentPrice: '650€',
            duration: '5 nuits / 6 jours',
            features: ['Shibuya Crossing', 'Temple Senso-ji', 'Tour de Tokyo']
        },
        {
            city: 'Kyoto',
            image: 'https://fr.japanspecialist.com/o/adaptive-media/image/689723/large/Destination_Kyoto_Old-streets-in-Kyoto.jpg?t=1641924637534',
            description: 'Ancienne capitale',
            originalPrice: '750€',
            currentPrice: '600€',
            duration: '4 nuits / 5 jours',
            features: ['Temple Kinkaku-ji', 'Quartier Gion', 'Sanctuaire Fushimi']
        },
        {
            city: 'Osaka',
            image: 'https://a.travel-assets.com/findyours-php/viewfinder/images/res70/477000/477571-Osaka.jpg',
            description: 'Cité gastronomique',
            originalPrice: '700€',
            currentPrice: '550€',
            duration: '4 nuits / 5 jours',
            features: ['Château d\'Osaka', 'Dotonbori', 'Universal Studios']
        },
        {
            city: 'Hiroshima',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKyGUcb6Z8rByNz2xPj-NYhoPrT75EadLipNp_np4sdzoB4kIzDLh1UaiCJPaj2J_Lchc&usqp=CAU',
            description: 'Ville de paix',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '3 nuits / 4 jours',
            features: ['Mémorial de la Paix', 'Miyajima', 'Château Hiroshima']
        },
        {
            city: 'Nara',
            image: 'https://fr.japanspecialist.com/o/adaptive-media/image/687627/large/pk0021_1_nara_toji_temple.jpg?t=1641924599708',
            description: 'Cité historique',
            originalPrice: '600€',
            currentPrice: '480€',
            duration: '3 nuits / 4 jours',
            features: ['Grand Bouddha', 'Parc aux cerfs', 'Temples anciens']
        }
    ],
    'Maroc': [
        {
            city: 'Marrakech',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSdjFNVt7xJnqUw_bCvTDZUhztGx9gahsNXIg&s',
            description: 'Ville impériale',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '4 nuits / 5 jours',
            features: ['Place Jemaa el-Fna', 'Souks', 'Jardin Majorelle']
        },
        {
            city: 'Fès',
            image: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQskwyFeXFXOLDGpDHOqjQzb19ax4oqvMn4Q&s',
            description: 'Cité médiévale',
            originalPrice: '300€',
            currentPrice: '240€',
            duration: '3 nuits / 4 jours',
            features: ['Médina classée', 'Tanneries', 'Université Al Quaraouiyine']
        },
        {
            city: 'Casablanca',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Métropole économique',
            originalPrice: '320€',
            currentPrice: '260€',
            duration: '3 nuits / 4 jours',
            features: ['Mosquée Hassan II', 'Corniche', 'Quartier Habous']
        },
        {
            city: 'Chefchaouen',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Ville bleue',
            originalPrice: '280€',
            currentPrice: '220€',
            duration: '3 nuits / 4 jours',
            features: ['Médina bleue', 'Montagnes Rif', 'Artisanat local']
        },
        {
            city: 'Essaouira',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Cité balnéaire',
            originalPrice: '300€',
            currentPrice: '240€',
            duration: '3 nuits / 4 jours',
            features: ['Plage', 'Médina fortifiée', 'Port de pêche']
        }
    ],
    'Turkiye': [
        {
            city: 'Istanbul',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Ville entre deux continents',
            originalPrice: '450€',
            currentPrice: '380€',
            duration: '4 nuits / 5 jours',
            features: ['Sainte-Sophie', 'Grand Bazar', 'Bosphore']
        },
        {
            city: 'Cappadoce',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Paysages lunaires',
            originalPrice: '500€',
            currentPrice: '420€',
            duration: '4 nuits / 5 jours',
            features: ['Vol en montgolfière', 'Villes souterraines', 'Cheminées de fées']
        },
        {
            city: 'Antalya',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Riviera turque',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '4 nuits / 5 jours',
            features: ['Vieille ville', 'Chutes de Duden', 'Plages']
        },
        {
            city: 'Pamukkale',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Sources thermales',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Piscines thermales', 'Hiérapolis', 'Travertins blancs']
        },
        {
            city: 'Éphèse',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Site antique',
            originalPrice: '380€',
            currentPrice: '310€',
            duration: '3 nuits / 4 jours',
            features: ['Bibliothèque de Celsus', 'Théâtre antique', 'Maisons en terrasse']
        }
    ],
    'Allemagne': [
        {
            city: 'Berlin',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Capitale historique',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '3 nuits / 4 jours',
            features: ['Mur de Berlin', 'Porte de Brandebourg', 'Île aux Musées']
        },
        {
            city: 'Munich',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Capitale bavaroise',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '3 nuits / 4 jours',
            features: ['Oktoberfest', 'Château de Neuschwanstein', 'Marienplatz']
        },
        {
            city: 'Hambourg',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Port maritime',
            originalPrice: '380€',
            currentPrice: '300€',
            duration: '3 nuits / 4 jours',
            features: ['Port de Hambourg', 'Speicherstadt', 'Miniatur Wunderland']
        },
        {
            city: 'Francfort',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Cœur financier',
            originalPrice: '350€',
            currentPrice: '280€',
            duration: '3 nuits / 4 jours',
            features: ['Skyline', 'Römerberg', 'Musées du Main']
        },
        {
            city: 'Cologne',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Ville du carnaval',
            originalPrice: '320€',
            currentPrice: '250€',
            duration: '3 nuits / 4 jours',
            features: ['Cathédrale de Cologne', 'Musée du chocolat', 'Quartier belge']
        }
    ],
    'Brésil': [
        {
            city: 'Rio de Janeiro',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Ville du carnaval',
            originalPrice: '700€',
            currentPrice: '550€',
            duration: '5 nuits / 6 jours',
            features: ['Christ Rédempteur', 'Copacabana', 'Pain de Sucre']
        },
        {
            city: 'São Paulo',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Métropole économique',
            originalPrice: '600€',
            currentPrice: '480€',
            duration: '4 nuits / 5 jours',
            features: ['Musée d\'art', 'Avenida Paulista', 'Diversité culinaire']
        },
        {
            city: 'Salvador de Bahia',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Cœur afro-brésilien',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '4 nuits / 5 jours',
            features: ['Pelourinho', 'Plage de Porto da Barra', 'Capoeira']
        },
        {
            city: 'Foz do Iguaçu',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Chutes impressionnantes',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '3 nuits / 4 jours',
            features: ['Chutes d\'Iguaçu', 'Parc des oiseaux', 'Barrage d\'Itaipu']
        },
        {
            city: 'Manaus',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Porte d\'entrée de l\'Amazonie',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '5 nuits / 6 jours',
            features: ['Rencontre des eaux', 'Opéra Amazonas', 'Excursions en forêt']
        }
    ],
    'Canada': [
        {
            city: 'Toronto',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Ville multiculturelle',
            originalPrice: '600€',
            currentPrice: '480€',
            duration: '4 nuits / 5 jours',
            features: ['Tour CN', 'Chutes du Niagara', 'Quartier Distillery']
        },
        {
            city: 'Montréal',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Métropole francophone',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '4 nuits / 5 jours',
            features: ['Vieux-Montréal', 'Mont Royal', 'Festivals']
        },
        {
            city: 'Vancouver',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Port du Pacifique',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '5 nuits / 6 jours',
            features: ['Stanley Park', 'Capilano Bridge', 'Gastown']
        },
        {
            city: 'Québec',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Joyau historique',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '3 nuits / 4 jours',
            features: ['Vieux-Québec', 'Château Frontenac', 'Village Huron']
        },
        {
            city: 'Calgary',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Porte des Rocheuses',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '3 nuits / 4 jours',
            features: ['Stampede', 'Parc national Banff', 'Tour Calgary']
        }
    ],
    'Etats-Unis': [
        {
            city: 'New York',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'La grosse pomme',
            originalPrice: '750€',
            currentPrice: '600€',
            duration: '4 nuits / 5 jours',
            features: ['Times Square', 'Statue de la Liberté', 'Central Park']
        },
        {
            city: 'Los Angeles',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Cité des anges',
            originalPrice: '700€',
            currentPrice: '560€',
            duration: '5 nuits / 6 jours',
            features: ['Hollywood', 'Santa Monica', 'Universal Studios']
        },
        {
            city: 'Las Vegas',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Cité du jeu',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '3 nuits / 4 jours',
            features: ['Strip', 'Spectacles', 'Casinos']
        },
        {
            city: 'San Francisco',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Ville des collines',
            originalPrice: '600€',
            currentPrice: '480€',
            duration: '4 nuits / 5 jours',
            features: ['Golden Gate', 'Alcatraz', 'Cable cars']
        },
        {
            city: 'Miami',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Plages et art déco',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '4 nuits / 5 jours',
            features: ['South Beach', 'Little Havana', 'Everglades']
        }
    ],
    'Australie': [
        {
            city: 'Sydney',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Ville des kangourous',
            originalPrice: '850€',
            currentPrice: '700€',
            duration: '5 nuits / 6 jours',
            features: ['Opéra de Sydney', 'Harbour Bridge', 'Bondi Beach']
        },
        {
            city: 'Melbourne',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Capitale culturelle',
            originalPrice: '800€',
            currentPrice: '650€',
            duration: '5 nuits / 6 jours',
            features: ['Laneways', 'Phillip Island', 'Great Ocean Road']
        },
        {
            city: 'Gold Coast',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Paradise surf',
            originalPrice: '750€',
            currentPrice: '600€',
            duration: '5 nuits / 6 jours',
            features: ['Surfers Paradise', 'Parcs à thème', 'Hinterland']
        },
        {
            city: 'Perth',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Ville isolée',
            originalPrice: '900€',
            currentPrice: '720€',
            duration: '5 nuits / 6 jours',
            features: ['Kings Park', 'Rottnest Island', 'Swan Valley']
        },
        {
            city: 'Cairns',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Porte de la Grande Barrière',
            originalPrice: '950€',
            currentPrice: '760€',
            duration: '6 nuits / 7 jours',
            features: ['Plongée sous-marine', 'Daintree Rainforest', 'Îles Whitsunday']
        }
    ],
    'Thailande': [
        {
            city: 'Bangkok',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Capitale vibrante',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '4 nuits / 5 jours',
            features: ['Temples bouddhistes', 'Marchés flottants', 'Vie nocturne']
        },
        {
            city: 'Phuket',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Perle d\'Andaman',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '5 nuits / 6 jours',
            features: ['Plages de sable blanc', 'Îles Phi Phi', 'Vieille ville']
        },
        {
            city: 'Chiang Mai',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Rose du Nord',
            originalPrice: '450€',
            currentPrice: '360€',
            duration: '4 nuits / 5 jours',
            features: ['Temples anciens', 'Doi Suthep', 'Marchés nocturnes']
        },
        {
            city: 'Krabi',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Paradis calcaire',
            originalPrice: '480€',
            currentPrice: '380€',
            duration: '5 nuits / 6 jours',
            features: ['Railay Beach', 'Tiger Cave Temple', 'Îles environnantes']
        },
        {
            city: 'Pattaya',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Station balnéaire',
            originalPrice: '400€',
            currentPrice: '320€',
            duration: '4 nuits / 5 jours',
            features: ['Plage de Jomtien', 'Sanctuaire de la Vérité', 'Île de Koh Larn']
        }
    ],
    'Norvège': [
        {
            city: 'Oslo',
            image: 'https://i.imgur.com/7QYQYJh.jpg',
            description: 'Capitale des fjords',
            originalPrice: '600€',
            currentPrice: '500€',
            duration: '4 nuits / 5 jours',
            features: ['Musées Vikings', 'Parc Vigeland', 'Opéra d\'Oslo']
        },
        {
            city: 'Bergen',
            image: 'https://i.imgur.com/KYQ4ZJh.jpg',
            description: 'Porte des fjords',
            originalPrice: '650€',
            currentPrice: '520€',
            duration: '5 nuits / 6 jours',
            features: ['Bryggen', 'Fløyen', 'Fjords environnants']
        },
        {
            city: 'Tromsø',
            image: 'https://i.imgur.com/5XrYQxW.jpg',
            description: 'Porte de l\'Arctique',
            originalPrice: '700€',
            currentPrice: '560€',
            duration: '5 nuits / 6 jours',
            features: ['Aurores boréales', 'Arctic Cathedral', 'Téléphérique']
        },
        {
            city: 'Trondheim',
            image: 'https://i.imgur.com/9XhTQzW.jpg',
            description: 'Ville historique',
            originalPrice: '550€',
            currentPrice: '440€',
            duration: '4 nuits / 5 jours',
            features: ['Cathédrale de Nidaros', 'Vieille ville', 'Musée Ringve']
        },
        {
            city: 'Ålesund',
            image: 'https://i.imgur.com/8QyQYJh.jpg',
            description: 'Joyau art nouveau',
            originalPrice: '500€',
            currentPrice: '400€',
            duration: '4 nuits / 5 jours',
            features: ['Archipel environnant', 'Mont Aksla', 'Musée Jugendstil']
        }
    ]
};

// Affichage des destinations
document.addEventListener('DOMContentLoaded', function() {
    const searchData = JSON.parse(localStorage.getItem('searchData')) || {};
    
    if (searchData.country) {
        document.getElementById('country-title').textContent = `Destinations disponibles en ${searchData.country}`;
    }

    const container = document.getElementById('destinations-container');
    container.innerHTML = '';
    
    if (searchData.country && destinationsByCountry[searchData.country]) {
        destinationsByCountry[searchData.country].forEach(destination => {
            const card = document.createElement('div');
            card.className = 'destination-card';
            
            card.innerHTML = `
                <div class="destination-image">
                    <img src="${destination.image}" alt="${destination.city}">
                </div>
                <div class="destination-info">
                    <h2>${destination.city}</h2>
                    <p>${destination.description}</p>
                    <div>
                        <span class="price">${destination.currentPrice}</span>
                        <span class="original-price">${destination.originalPrice}</span>
                    </div>
                    <p>${destination.duration}</p>
                    <ul>
                        ${destination.features.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                    <a href="#" class="see-more">Voir l'offre</a>
                </div>
            `;
            container.appendChild(card);
        });
    } else {
        container.innerHTML = '<p>Aucune destination disponible pour ce pays actuellement.</p>';
    }
});


// script.js (version modifiée avec bouton Réserver)
document.addEventListener('DOMContentLoaded', function() {
    const searchData = JSON.parse(localStorage.getItem('searchData')) || {};
    
    if (searchData.country) {
        document.getElementById('country-title').textContent = `Destinations disponibles en ${searchData.country}`;
    }

    const container = document.getElementById('destinations-container');
    container.innerHTML = '';
    
    if (searchData.country && destinationsByCountry[searchData.country]) {
        destinationsByCountry[searchData.country].forEach(destination => {
            const card = document.createElement('div');
            card.className = 'destination-card';
            
            card.innerHTML = `
                <div class="destination-image">
                    <img src="${destination.image}" alt="${destination.city}">
                </div>
                <div class="destination-info">
                    <h2>${destination.city}</h2>
                    <p>${destination.description}</p>
                    <div class="price-container">
                        <span class="price">${destination.currentPrice}</span>
                        <span class="original-price">${destination.originalPrice}</span>
                    </div>
                    <p>${destination.duration}</p>
                    <ul>
                        ${destination.features.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                    <a href="#" class="see-more">Voir l'offre</a>
                    <button class="reserve-btn">Réserver</button>
                </div>
            `;
            container.appendChild(card);
        });

        // Ajout du style pour le bouton Réserver
        const style = document.createElement('style');
        style.textContent = `
            .reserve-btn {
                display: block;
                background: linear-gradient(90deg, #888888, #2E7D32);
                color: white;
                text-align: center;
                padding: 12px;
                margin-top: 10px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 500;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                width: 100%;
            }
            .reserve-btn:hover {
                background: linear-gradient(90deg, #2E7D32, #4CAF50);
                box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
            }
        `;
        document.head.appendChild(style);
    } else {
        container.innerHTML = '<p>Aucune destination disponible pour ce pays actuellement.</p>';
    }
});
// Nouvelle fonction pour ouvrir Google Maps
function setupGoogleMapsButton() {
    const searchData = JSON.parse(localStorage.getItem('searchData')) || {};
    const country = searchData.country || 'Tunisie';
    
    if (!destinationsByCountry[country]) return;
  
    // Coordonnées centrales du pays (exemple pour la Tunisie)
    const countryCenterCoords = {
      'Tunisie': '36.8,10.2',
      'France': '46.5,2.0',
      'Rome': '41.9,12.5',
      // Ajoutez les autres pays...
    };
  
    const button = document.getElementById('open-google-maps');
    if (button) {
      button.onclick = function() {
        // Créer un lien avec toutes les destinations
        let mapsUrl = `https://www.google.com/maps?q=${countryCenterCoords[country] || '36.8,10.2'}`;
        
        // Ajouter chaque destination comme point d'intérêt
        destinationsByCountry[country].forEach(dest => {
          const city = encodeURIComponent(dest.city);
          mapsUrl += `&q=${city}`;
        });
  
        window.open(mapsUrl, '_blank');
      };
    }
  }
  
  // Appeler cette fonction après le chargement
  document.addEventListener('DOMContentLoaded', function() {
    setupGoogleMapsButton();
  });