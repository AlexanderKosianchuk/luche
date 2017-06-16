import React from 'react';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _isEmpty from 'lodash.isempty';

import redirect from 'actions/redirect';
import setTemplate from 'actions/setTemplate';

class ShowChartButton extends React.Component {
    buildButton() {
        if (_isEmpty(this.props.fdrCyclo.chosenAnalogParams)) {
            return '';
        }

        return <span
            className='glyphicon glyphicon-picture'
            aria-hidden='true'>
        </span>;
    }

    showChart()
    {
        let templateName = 'last';
        Promise.resolve(this.props.setTemplate({
            flightId: this.props.flightId,
            templateName: templateName,
            analogParams: this.props.fdrCyclo.chosenAnalogParams,
            binaryParams: this.props.fdrCyclo.chosenBinaryParams
        })).then(() => {
            this.props.redirect('/chart/'
                + 'flight-id/'+ this.props.flightId + '/'
                + 'template-name/'+ templateName + '/'
                + 'from-frame/'+ this.props.startFrame + '/'
                + 'to-frame/'+ this.props.endFrame
            );
        });
    }

    render() {
        return <ul className='nav navbar-nav navbar-right'>
          <li><a href='#' onClick={ this.showChart.bind(this) }>
              { this.buildButton() }
          </a></li>
        </ul>;
    }
}

function mapStateToProps (state) {
    return {
        fdrCyclo: state.fdrCyclo,
        startFrame: state.flightInfo.selectedStartFrame,
        endFrame: state.flightInfo.selectedEndFrame
    }
}

function mapDispatchToProps(dispatch) {
    return {
        setTemplate: bindActionCreators(setTemplate, dispatch),
        redirect: bindActionCreators(redirect, dispatch)
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ShowChartButton);