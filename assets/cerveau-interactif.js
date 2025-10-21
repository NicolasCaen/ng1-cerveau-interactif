        // Initialiser les interactions au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            initializeInteractions();
        });

        let currentActiveZone = null;
        let activeZones = {};
        let zoneTimeouts = {};
        const zones = [
            'meninges',
            'cervelet',
            'lobes-temporaux',
            'lobes-occipitaux',
            'lobes-parietaux',
            'lobes-frontaux'
        ];

        function initializeInteractions() {

            // Réorganiser le SVG pour mettre les zones et boutons à la fin (au-dessus)
            const svg = document.querySelector('svg');
            const cerveletDisable = document.getElementById('zone-cervelet-disable');
            
            if (svg) {
                // D'abord mettre zone-cervelet-disable
                if (cerveletDisable && cerveletDisable.parentNode === svg) {
                    svg.appendChild(cerveletDisable);
                }
                
                // Ensuite mettre toutes les zones par-dessus
                zones.forEach(zone => {
                    const zoneElement = document.getElementById(`zone-${zone}`);
                    const btnElement = document.getElementById(`btn-${zone}`);
                    
                    // Déplacer les zones à la fin du SVG (au-dessus de cervelet-disable)
                    if (zoneElement && zoneElement.parentNode === svg) {
                        svg.appendChild(zoneElement);
                    }
                    
                    // Déplacer les boutons à la fin du SVG
                    if (btnElement && btnElement.parentNode === svg) {
                        svg.appendChild(btnElement);
                    }
                });
            }

            applyDefaultState();

            zones.forEach(zone => {
                const zoneElement = document.getElementById(`zone-${zone}`);
                const fondElement = document.getElementById(`fond-${zone}`);
                const btnElement = document.getElementById(`btn-${zone}`);
                const infoElement = document.getElementById(`info-${zone}`);

                if (zoneElement) {
                    zoneElement.addEventListener('mouseenter', () => {
                        // Annuler le timeout de sortie si existant
                        if (zoneTimeouts[zone]) {
                            clearTimeout(zoneTimeouts[zone]);
                            delete zoneTimeouts[zone];
                        }
                        
                        // Vérifier si la zone est déjà active
                        if (activeZones[zone]) {
                            return; // Ne rien faire si déjà active
                        }

                        zones.forEach(otherZone => {
                            if (otherZone !== zone) {
                                const otherFond = document.getElementById(`fond-${otherZone}`);
                                if (otherFond) {
                                    otherFond.style.opacity = '0';
                                    otherFond.style.pointerEvents = 'none';
                                    otherFond.classList.remove('active');
                                }

                                const otherBtn = document.getElementById(`btn-${otherZone}`);
                                if (otherBtn && otherZone !== 'meninges') {
                                    otherBtn.style.opacity = '0';
                                    otherBtn.style.pointerEvents = 'none';
                                    otherBtn.classList.remove('active');
                                }
                            }
                        });

                        if (zone !== 'meninges') {
                            const btnMeninges = document.getElementById('btn-meninges');
                            if (btnMeninges) {
                                btnMeninges.style.opacity = '0';
                                btnMeninges.style.pointerEvents = 'none';
                                btnMeninges.classList.remove('active');
                            }
                        }

                        activeZones[zone] = true;
                        console.log('🎯 ZONE SURVOLÉE:', zone);
                        
                        // Afficher le fond et le bouton
                        if (fondElement) {
                            fondElement.style.opacity = '1';
                            fondElement.style.pointerEvents = 'none';
                            fondElement.classList.add('active');
                        }
                        if (btnElement) {
                            btnElement.style.opacity = '1';
                            btnElement.style.pointerEvents = 'none';
                            btnElement.classList.add('active');
                        }
                        
                        // Afficher l'info box
                        if (infoElement) {
                            infoElement.classList.add('active');
                        }
                        
                        // Activer zone-cervelet-disable si c'est le cervelet
                        if (zone === 'cervelet') {
                            if (cerveletDisable) {
                                cerveletDisable.classList.add('active');
                                // Remettre cervelet-disable juste après le fond-cervelet mais avant les autres zones
                                const fondCervelet = document.getElementById('fond-cervelet');
                                if (fondCervelet && fondCervelet.parentNode && cerveletDisable.parentNode) {
                                    fondCervelet.parentNode.insertBefore(cerveletDisable, fondCervelet.nextSibling);
                                }
                            }
                        }
                        
                        currentActiveZone = zone;
                    });

                    zoneElement.addEventListener('mouseleave', () => {
                        // Ajouter un petit délai avant de réinitialiser
                        zoneTimeouts[zone] = setTimeout(() => {
                            resetZone(zone);
                            delete zoneTimeouts[zone];
                        }, 50);
                    });
                }
            });

            // Gestion spéciale de la zone cervelet-disable
            if (cerveletDisable) {
                cerveletDisable.addEventListener('mouseenter', () => {
                    console.log('🎯 ZONE SURVOLÉE: cervelet-disable');
                    // Réinitialiser la zone cervelet
                    resetZone('cervelet');
                });
            }
        }

        function applyDefaultState() {
            zones.forEach(zone => {
                const fondElement = document.getElementById(`fond-${zone}`);
                if (fondElement) {
                    fondElement.style.opacity = '1';
                    fondElement.style.pointerEvents = 'none';
                    fondElement.classList.add('active');
                }

                const btnElement = document.getElementById(`btn-${zone}`);
                if (btnElement) {
                    if (zone === 'meninges') {
                        btnElement.style.opacity = '1';
                        btnElement.style.pointerEvents = 'none';
                        btnElement.classList.add('active');
                    } else {
                        btnElement.style.opacity = '0';
                        btnElement.style.pointerEvents = 'none';
                        btnElement.classList.remove('active');
                    }
                }
            });
        }

        function resetZone(zone) {
            activeZones[zone] = false;
            const infoElement = document.getElementById(`info-${zone}`);

            if (infoElement) {
                infoElement.classList.remove('active');
            }

            // Désactiver zone-cervelet-disable si c'est le cervelet
            if (zone === 'cervelet') {
                const cerveletDisable = document.getElementById('zone-cervelet-disable');
                if (cerveletDisable) {
                    cerveletDisable.classList.remove('active');
                    // Remettre cervelet-disable au début (en dessous de tout)
                    const svg = document.querySelector('svg');
                    if (svg && cerveletDisable.parentNode === svg) {
                        const firstChild = svg.firstChild;
                        if (firstChild) {
                            svg.insertBefore(cerveletDisable, firstChild);
                        }
                    }
                }
            }

            if (currentActiveZone === zone) {
                currentActiveZone = null;
                applyDefaultState();
            }
        }