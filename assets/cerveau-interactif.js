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
        let initialFondOpacitySetting = { type: 'auto' };
        

        function initializeInteractions() {
            const svg = document.querySelector('svg');
            const cerveletDisable = document.getElementById('zone-cervelet-disable');

            initialFondOpacitySetting = resolveInitialFondOpacitySetting();

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
                    const { opacity, isActive } = getDefaultFondState(zone);
                    fondElement.style.opacity = opacity;
                    fondElement.style.pointerEvents = 'none';
                    fondElement.classList.toggle('active', isActive);
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

        function resolveInitialFondOpacitySetting() {
            const container = document.querySelector('.cerveau-container');

            if (!container) {
                return { type: 'auto' };
            }

            const rawValue = container.getAttribute('data-initial-fond-opacity');

            if (!rawValue || rawValue.trim().toLowerCase() === 'auto') {
                return { type: 'auto' };
            }

            const parsed = parseFloat(rawValue);

            if (!Number.isNaN(parsed)) {
                const clamped = Math.min(1, Math.max(0, parsed));
                return { type: 'fixed', value: clamped };
            }

            return { type: 'auto' };
        }

        function getDefaultFondState(zone) {
            if (zone === 'meninges') {
                return {
                    opacity: '0',
                    isActive: false
                };
            }

            if (initialFondOpacitySetting.type === 'fixed') {
                const opacity = String(initialFondOpacitySetting.value);
                return {
                    opacity,
                    isActive: initialFondOpacitySetting.value > 0
                };
            }

            const isVisible = zone !== 'meninges';
            return {
                opacity: isVisible ? '1' : '0',
                isActive: isVisible
            };
        }

        function applyExclusiveState(targetZone) {
            zones.forEach(zone => {
                const fondElement = document.getElementById(`fond-${zone}`);
                if (fondElement) {
                    const isTarget = zone === targetZone;
                    const shouldShow = isTarget;
                    fondElement.style.opacity = shouldShow ? '1' : '0';
                    fondElement.style.pointerEvents = 'none';
                    fondElement.classList.toggle('active', shouldShow);
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