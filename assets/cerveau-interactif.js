        document.addEventListener('DOMContentLoaded', function() {
            initializeInteractions();
        });

        const zones = [
            'meninges',
            'cervelet',
            'lobes-temporaux',
            'lobes-occipitaux',
            'lobes-parietaux',
            'lobes-frontaux'
        ];

        let activePopupZone = null;

        function initializeInteractions() {
            const svg = document.querySelector('svg');
            const cerveletDisable = document.getElementById('zone-cervelet-disable');

            if (svg) {
                if (cerveletDisable && cerveletDisable.parentNode === svg) {
                    const firstChild = svg.firstChild;
                    if (firstChild && firstChild !== cerveletDisable) {
                        svg.insertBefore(cerveletDisable, firstChild);
                    }
                }

                zones.forEach(zone => {
                    const zoneElement = document.getElementById(`zone-${zone}`);
                    const btnElement = document.getElementById(`btn-${zone}`);

                    if (zoneElement && zoneElement.parentNode === svg) {
                        svg.appendChild(zoneElement);
                    }

                    if (btnElement && btnElement.parentNode === svg) {
                        svg.appendChild(btnElement);
                    }
                });
            }

            applyDefaultState();

            zones.forEach(zone => {
                const zoneElement = document.getElementById(`zone-${zone}`);

                if (!zoneElement) {
                    return;
                }

                zoneElement.addEventListener('mouseenter', () => {
                    applyExclusiveState(zone);
                });

                zoneElement.addEventListener('mouseleave', () => {
                    if (activePopupZone) {
                        applyExclusiveState(activePopupZone);
                    } else {
                        applyDefaultState();
                    }
                });

                zoneElement.addEventListener('click', event => {
                    event.stopPropagation();
                    handleZoneClick(zone);
                });
            });

            document.addEventListener('click', handleOutsideClick);
        }

        function applyDefaultState() {
            zones.forEach(zone => {
                const fondElement = document.getElementById(`fond-${zone}`);
                if (fondElement) {
                    const visible = zone !== 'meninges';
                    fondElement.style.opacity = visible ? '1' : '0';
                    fondElement.style.pointerEvents = 'none';
                    fondElement.classList.toggle('active', visible);
                }

                const btnElement = document.getElementById(`btn-${zone}`);
                if (btnElement) {
                    btnElement.style.opacity = '1';
                    btnElement.style.pointerEvents = 'none';
                    btnElement.classList.add('active');
                }
            });

            closeAllPopups();
        }

        function applyExclusiveState(targetZone) {
            zones.forEach(zone => {
                const fondElement = document.getElementById(`fond-${zone}`);
                if (fondElement) {
                    const isTarget = zone === targetZone;
                    fondElement.style.opacity = isTarget ? '1' : '0';
                    fondElement.style.pointerEvents = 'none';
                    fondElement.classList.toggle('active', isTarget);
                }

                const btnElement = document.getElementById(`btn-${zone}`);
                if (btnElement) {
                    const isTarget = zone === targetZone;
                    btnElement.style.opacity = isTarget ? '1' : '0';
                    btnElement.style.pointerEvents = 'none';
                    btnElement.classList.toggle('active', isTarget);
                }
            });
        }

        function handleZoneClick(zone) {
            if (activePopupZone === zone) {
                activePopupZone = null;
                applyDefaultState();
                return;
            }

            activePopupZone = zone;
            applyExclusiveState(zone);
            openInfo(zone);
        }

        function handleOutsideClick(event) {
            const isZone = event.target.closest('.zone-action');
            if (isZone) {
                return;
            }

            activePopupZone = null;
            applyDefaultState();
        }

        function openInfo(zone) {
            zones.forEach(otherZone => {
                const infoElement = document.getElementById(`info-${otherZone}`);
                if (!infoElement) {
                    return;
                }

                if (otherZone === zone) {
                    infoElement.classList.add('active');
                } else {
                    infoElement.classList.remove('active');
                }
            });
        }

        function closeAllPopups() {
            zones.forEach(zone => {
                const infoElement = document.getElementById(`info-${zone}`);
                if (infoElement) {
                    infoElement.classList.remove('active');
                }
            });
        }